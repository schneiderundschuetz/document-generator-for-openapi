# WordPress Document Generator for OpenAPI

## Description

This plugin generates OpenAPI (fka. Swagger) documents for a given WordPress REST Api namespace.
  
## How it works

This plugin reads the schema definition of a given WordPress REST Api namespace and transforms it to a
OpenAPI document. The generator itself is exposed via the WordPress REST Api with the namespace ``document-generator-for-openapi/v1``.

There is also a built in WP-CLI Command.
## Usage

1. Install plugin via WordPress Plugin repository or by manually copying files
2. Activate the plugin
3. Access the REST Api of the generator by calling ``/wp-json/document-generator-for-openapi/v1/document?namespace=\<NAMESPACE\>`` and your OpenAPI document will be returned.
4. Or use the integrated WP-CLI command ``wp openapi-generator export-file`` 

## Limitations and known issues

* Beware that currently the generator is exposeed to anonymous users since the WordPress schema endpoint is also publicly 
available. Use it at your own risk or disable the plugin after use.
* No UI for configuration yet
* Currently only version 3.1.0 of the OpenAPI specification is implemented. Swagger tools for 3.0.0 might work though.
* Unittests need to be extended and improved
* Extensibility with hooks needs to be improved