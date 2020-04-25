# alfred-wordpress-developer-workflow
Alfred workflow to search the [WordPress developer reference](https://developer.wordpress.org/reference/)

See [this forum topic](http://www.alfredforum.com/topic/4321-wordpress-developer-reference/) for more information.

Searching WordPress core made easy. Finding results is very fast because it searches in local JSON files (instead of an online API).
The JSON files included in this workflow are created with the [WP Parser JSON plugin](https://github.com/keesiemeijer/wp-parser-json)

![screeshot-2](https://user-images.githubusercontent.com/1436618/27253218-ad22f4c0-5370-11e7-936d-43129b91f36f.png)

The workflow checks every 2 weeks if there are new updates in WordPress core. Or you can check manually for updates with the "wpdev update" keyword.

![screeshot-1](https://user-images.githubusercontent.com/1436618/27253217-aad88e32-5370-11e7-9a18-49cd60d5c540.png)

# Update Instructions
To update this workflow to the newest version [click this link](https://github.com/keesiemeijer/alfred-wordpress-developer-workflow/raw/master/wordpress-developer.alfredworkflow).

# Changelog
* version 2.0
	* Fix Curl issue with permanently moved (redirected) files
	* Order pinned (update workflow) results at the top
* version 1.0
	* Add ability to update the workflow from within the alfred app.
