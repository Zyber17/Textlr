# Textlr API Outline

API will be located at `http://textlr.org/api/v0/`. All text uploads are referred to as “texts” as is the proper term for them. All responses will be in plain text (unless otherwise specified)

Currently, there is no rate limiting. Should the API be abused, all clients abusing it will be perma-banned and rate limiting will be implemented.

All API requests require the application to include a `client_key` to identify the application using the API. One can be requested by emailing <hello@zyber17.com>.

----------

## GET Requests
### 1. Retrieve a post
* Location: `http://textlr.org/api/v0/retrieve`
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
	* `400`: Your request made no sense
	* `404`: Text not found
	* `500`: Something went wrong. Textlr has no idea why. So yeah…

		


[^1]: This regex is still a WIP and is highly subject to change.