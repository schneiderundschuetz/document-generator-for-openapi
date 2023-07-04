=== Plugin Name ===
Contributors: schneiderundschuetz
Donate link: https://www.schneiderundschuetz.com/
Tags: api, openapi, swagger, rest api, generator
Requires at least: 5.7
Tested up to: 6.2
Stable tag: 1.1.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

OpenAPI (fka. Swagger) Document Generator for WordPress REST API

== Description ==

This plugin reads the schema definition of a given WordPress REST Api namespace and transforms it to a
OpenAPI document. The generator itself is exposed via the WordPress REST Api with the namespace document-generator-for-openapi/v1.

There is also a built in WP-CLI Command.

=== Limitations ===
* Beware that currently the generator is exposeed to anonymous users since the WordPress schema endpoint is also publicly 
available. Use it at your own risk or disable the plugin after use.
* No UI for configuration yet
* Currently only version 3.1.0 of the OpenAPI specification is implemented. Swagger tools for 3.0.0 might work though.
* Extensibility with hooks needs to be improved


== Installation ==

1. Install plugin via WordPress Plugin repository or by manually copying files
2. Activate the plugin
3. Access the REST Api of the generator by calling /wp-json/document-generator-for-openapi/v1/document?namespace=\<NAMESPACE\> and your OpenAPI document will be returned.
4. Or use the integrated WP-CLI command wp openapi-generator export-file

== Frequently Asked Questions ==


== Screenshots ==

* No screenshots yet

== Changelog ==
= 1.1.0 =
- Added WP-CLI command to export OpenAPI document via CLI (Thanks to @vnmedeiros - Vinícius Nunes Medeiros)

= 1.0.2 =
- Added missing files for WordPress plugin repository

= 1.0.1 =
- Changed name of plugin from openapi-generator to document-generator-for-openapi

= 1.0.0 =
- Initial release
