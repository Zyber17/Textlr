# Textlr API Outline

## Basic Info

API will be located at `http://textlr.org/api.php`. All text uploads are referred to as “texts” as is the proper term for them. All responses will be in JSON. Also, please note this entire API is alpha and subject to change.

Currently, there is no rate limiting. Should the API be abused, all clients abusing it will be perma-banned and rate limiting will be implemented. Eventually, the API might be paid, but, if it does become paid, it’ll just cost enough to where I can cover the hosting. (We’re talking like $15/yr for unlimited usage.)

All API requests require the application to include a `client_key` to identify the application using the API. One can be requested by emailing <hello@zyber17.com>.

----------

## Requests

### GET
#### Retrieve a post
* Location: `http://textlr.org/api.php`
* Type of request: GET
* Parameters
	* `client_key`: The unique identifier assigned to your application.
	* `post_id`: The ID of the specific text you want to retrieve.
		* Use the regular expression `/(?:(?:[\w-]+\/)|(?<!.))(\w{5})(?:\.txt)?(?!.)/` (after stripping off `http://textlr.org/`) to find the `post_id`. The key should be the only capture returned.[^1]
	* *Optional*, `plain`: Either true or false. (Assumed false if unspecified.)
		* *If true*: the text pre-HTML conversion will be returned.
		* *If false/unspecified*: the HTML converted text will be returned.
* Response (code `200`)
	* The requested text will be returned in the format requested. (See: Parameters.`plain`)
* Errors
	* `401`: Your API key was invalid.
	* `404`: Text not found.

## POST
### Create a post
* Location: `http://textlr.org/api.php`
* Type of request: POST
* Parameters
	* `client_key`: The unique identifier assigned to your application.
	* `text`: The text of text to be uploaded.
* Response (code `200`)
	* The URL slug for that text will be returned.
		* Keep in mind that you’ll need to prepend `http://textlr.org/` to the slug.
* Applicable Errors
	* `401`: Your API key was invalid.
	* `403`: The text was too short, it must be longer than two characters.
	* `507`: Textlr could not find an available URL slug to assign to the text trying to be uploaded.

----------

## Responses

### Success

#### Generic GET success response example[^2]

	{
		"response":{
			"code":200
		},
		"data":{
			"text":"<p>text<p>",
			"title":"title"
		}
	}

Please note that the title paramater will only be returned if a title exists.

#### Generic POST success response example

	{
		"response":{
			"code":200
		},
		"data":{
			"url":"description/code",
			"title":"title"
		}
	}

Please note that the title paramater will only be returned if a title exists.

### Error

#### Generic error response example

    {
    	"errors":{
    		"code":000,
    		"message":"Some reason"
    	}
    }


#### List of error codes
* `401`: Your API key was invalid. (GET and POST)
* `404`: Text not found. (GET)
* `507`: Textlr could not find an available URL slug to assign to the text trying to be uploaded. (POST)


[^1]: This regex is still a WIP and is highly subject to change.
[^2]: Is the same for plain being true and untrue.