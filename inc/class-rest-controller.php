<?php

namespace OpenAPIGenerator;

class RestController {
    public function __construct() {
        $this->namespace = 'document-generator-for-openapi/v1';
        $this->resource_name = 'document';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name, [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'generate_document' ],
                'permission_callback' => [ $this, 'generate_document_permission_check' ],
                'args' => $this->get_generate_document_args()
            ],
            'schema' => [ $this, 'get_generate_document_schema' ]
        ]);
    }

    public function generate_document_permission_check( $request ) {
        return true;
    }

    public function generate_document( $request ) {
        $namespace = '';
        $extract_common_types = false;

        if ( isset( $request['namespace'] ) ) {
            $namespace = $request['namespace'];
        }

        if ( isset( $request['extract_common_types'] ) ) {
            $extract_common_types = $request['extract_common_types'] === true;
        }

        if ( empty( $namespace ) ) {
            return new \WP_Error( 'namespace_empty',
                                    esc_html__( 'The namespace needs to be defined',
                                                'document-generator-for-openapi' ),
                                    [ 'status' => 400 ] );
        }

        //the namespace needs to exist
        if ( !in_array( $namespace, rest_get_server()->get_namespaces() ) ) {
            return new \WP_Error( 'namespace_not_found',
                                    esc_html__( 'The namespace is invalid',
                                                'document-generator-for-openapi' ),
                                    [ 'status' => 400 ] );
        }

        //get wordpress rest schema
        $routes = rest_get_server()->get_routes( $request['namespace'] );
        
        $data = rest_get_server()->get_data_for_routes( $routes, 'help' );

        //generate openapi document
        //TODO create factory for switching between version
        $generator = new Generator3_1_0( $namespace, $data, $extract_common_types );
        $result = $generator->generateDocument();
        
        return rest_ensure_response($result);
    }

    public function get_generate_document_args() {
        return [
            'namespace' => [
                'description' => esc_html__( 'The namespace for which the OpenAPI document should be generated.',
                                                'document-generator-for-openapi' ),
                'type' => 'string',
                'required' => true
            ],
            'extract_common_types' => [
                'description' => esc_html__( 'Defines if JSON schema objects should be extracted and, if equal, merged to one single named type.',
                                                'document-generator-for-openapi' ),
                'type' => 'boolean',
                'required' => false
            ]
        ];
    }

    public function get_generate_document_schema() {

        //TODO is this a legal JSON schema?
        return [
            '$schema'    => 'https://json-schema.org/draft/2020-12/schema',
            '$ref' => 'https://spec.openapis.org/oas/3.1/schema/2021-05-20'
        ];

    }
}