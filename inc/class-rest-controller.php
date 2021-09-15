<?php

namespace OpenAPIGenerator;

class RestController {
    public function __construct() {
        $this->namespace = 'openapi-generator/v1';
        $this->resource_name = 'schema';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name, [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'generate_schema' ],
                'permission_callback' => [ $this, 'generate_schema_permission_check' ],
                'args' => $this->get_generate_schema_args()
            ]
        ]);
    }

    public function generate_schema_permission_check( $request ) {
        return true;
    }

    public function generate_schema( $request ) {
        $namespace = '';

        if ( isset( $request['namespace'] )) {
            $namespace = $request['namespace'];
        }

        if ( empty( $namespace )) {
            return new \WP_Error( 'namespace_empty', esc_html__( 'The namespace needs to be defined', 'openapi-generator' ), [ 'status' => 400 ] );
        }

        //get wordpress rest schema
        $routes = rest_get_server()->get_routes( $request['namespace'] );
        $data = rest_get_server()->get_data_for_routes( $routes );

        //generate openapi schema
        $generator = new Generator3_1_0( $data );
        $result = $generator->generateSchema();
        
        return rest_ensure_response($result);
    }

    public function get_generate_schema_args() {
        return [
            'namespace' => [
                'description' => esc_html__( 'The namespace for which the OpenAPI schema should be generated.', 'openapi-generator' ),
                'type' => 'string',
                'required' => 'true'
            ]
        ];
    }
}