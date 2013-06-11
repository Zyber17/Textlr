# Simple Text Uploading

Textlr is a open source platform for uploaded text for easy sharing. It uses [Markdown](http://daringfireball.net/projects/markdown/) and has other nifty features baked in. The official Textlr web service is available to use at [textlr.org](http://textlr.org/), but anyone is free to host it.

Current version: `2.0`.

## Roadmap

`@v:x.x.x` is a tag to describe the version of Textlr that the roadmap items are slated for.

1. Style dialogue boxes more proactively. @v:2.1
2. Improve handling of special characters in descriptive URLs. @v:2.1
3. Improve styling of titles on mobile devices. @v:2.1
4. Clean up the code (Especially the JavaScript). @v:2.1

## Installation

Installing it is rather easy. Just change the credentials in `db.php` to your credentials and you're pretty much done. A few things might be hard coded to point to textlr.org, but that's easily changeable.

___________


## Changes in 2.0

Textlr 2.0 is a significant refresh to Textlr. Listed below are the numerous completed changes.

### Changes
* Partial rewrite of PHP
* Partial rewrite of JavaScript
* Prettier links made with `<http://link.tld>`
* UI improvements
* Sharing to <http://App.net>
* Markdown previews are now rendered client-side rather than server-side[^1]

### Additions
* API
* Subtle branding to text pages
* Support for uploaded more than one text without refreshing the page


[^1]: Subject to change.