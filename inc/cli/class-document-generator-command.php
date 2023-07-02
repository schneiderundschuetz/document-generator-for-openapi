<?php

namespace OpenAPIGenerator\cli;

use WP_CLI\Utils;

/**
 * Generates OpenAPI json documents based on the WordPress REST Api schema.
 */
class DocumentGenerator_Command
{

	/**
	 * Export the OpenAPI document of a given namespace as a JSON file.
	 *
	 * ## OPTIONS 
	 *
	 * [<namespace>]
	 * : The namespace for which the OpenAPI document should be generated.
	 * 
	 * [--destination]
	 * : File name of the resulting OpenAPI JSON file.
	 *
	 * [--extract-common-types]
	 * : Defines if JSON schema objects should be extracted and, if equal, merged to one single named type (default=false).
	 * 
	 * @subcommand export-file
	 */
	public function export_file($args, $assoc_args)
	{
		$namespace = isset($args[0]) ? $args[0] : 'wp/v2';
		$output = isset($assoc_args['destination']) ? $assoc_args['destination'] : 'openapi.json';
		$extract_common_types      = (bool) Utils\get_flag_value( $assoc_args, 'extract-common-types', false );

		if (empty($namespace)) {
			\WP_CLI::error('The namespace needs to be defined', true);
		}

		if (!in_array($namespace, rest_get_server()->get_namespaces())) {
			\WP_CLI::error('The namespace is invalid', true);
		}

		$this->do_export_file($namespace, $extract_common_types, $output);
	}

	private function do_export_file($namespace, $extract_common_types, $output)
	{
		//get wordpress rest schema
		$routes = rest_get_server()->get_routes($namespace);
		$data = rest_get_server()->get_data_for_routes($routes, 'help');

		//generate openapi document
		//TODO create factory for switching between version
		$generator = new \OpenAPIGenerator\Generator3_1_0($namespace, $data, $extract_common_types);
		$result = $generator->generateDocument();

		//write json to file
		if (file_put_contents($output, json_encode($result))) {
			\WP_CLI::success("The JSON file ($output) created successfully.");
		} else {
			\WP_CLI::error("Error creating json file: $output", true);
		}
	}
}

?>