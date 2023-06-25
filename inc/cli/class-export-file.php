<?php 

namespace OpenAPIGenerator\cli;

class ExportFile {
	
	/**
	 * export JSON file with especification openapi
	 *
	 * ## OPTIONS 
	 *
	 * [--output]
	 * : file name to output file with JSON openapi content 
	 *
	 * [--namespace]
	 * : the namespace for which the OpenAPI document should be generated.
	 * 
	 * [--extract_common_types]
	 * : defines if JSON schema objects should be extracted and, if equal, merged to one single named type (defaul=false).
	 */
	public function __invoke($args, $assoc_args) {
		
		$output = isset($assoc_args['output']) ? $assoc_args['output'] : 'openapi.json';
		$namespace = isset($assoc_args['namespace']) ? $assoc_args['namespace'] : 'wp/v2';
		$extract_common_types = isset($assoc_args['extract_common_types']) ? $assoc_args['extract_common_types'] : false;

		if ( empty( $namespace ) )
		{
			\WP_CLI::error( 'The namespace needs to be defined', true );
		}

		if ( !in_array( $namespace, rest_get_server()->get_namespaces() ) )
		{
			\WP_CLI::error( 'The namespace is invalid', true );
		}

		$this->export_file($namespace, $extract_common_types, $output);
	}

	private function export_file($namespace, $extract_common_types, $output) {
		//get wordpress rest schema
		$routes = rest_get_server()->get_routes( $namespace );
		$data = rest_get_server()->get_data_for_routes( $routes, 'help' );

		//generate openapi document
		//TODO create factory for switching between version
		$generator = new \OpenAPIGenerator\Generator3_1_0( $namespace, $data, $extract_common_types );
		$result = $generator->generateDocument();
        $openapiJSON = json_encode($result);

        //write json to file
        if (file_put_contents($output, $openapiJSON))
            \WP_CLI::success("The JSON file ($output) created successfully.");
        else 
            \WP_CLI::error( "Error creating json file: $output", true );
	}
}

?>