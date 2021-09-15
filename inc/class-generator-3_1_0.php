<?php

namespace OpenAPIGenerator;

class Generator3_1_0 extends GeneratorBase {

    protected $schemaObjects = [];

    public function generateDocument() {
        return apply_filters( 'openapi_generator_v3_1', $this->generateRoot(), $this);
    }

    public function generateRoot () {
        return [
            'openapi' => '3.1.0',
            'info' => $this->generateInfo(),
            'jsonSchemaDialect' => 'http://json-schema.org/draft-04/schema#',
            'servers' => $this->generateServers(),
            'paths' => $this->generatePaths(),
            'security' => $this->generateSecurity(),
            'components' => [
                'schemas' => $this->schemaObjects
            ]
        ];
    }

    public function generateInfo() {
        return [
            'title' => $this->namespace,
            'summary' => esc_html(
                            sprintf(
                                esc_html__( 'Generated OpenAPI document of the namespace %s on %s.',
                                            'openapi-generator') ,
                                            $this->namespace,
                                            get_option( 'blogname' )
                                )
                            ),
            'version' => '1'
        ];
    }

    public function generateServers() {
        return [
            [ 'url' => rest_url( $this->namespace ) ]
        ];
    }

    public function generatePaths() {

        $result = [];

        foreach ($this->routes as $url => $spec) {
            //remove namespace portion from url
            $url = preg_replace( '#' . $this->namespace . '/?#' , '', $url );

            //create OpenAPI style substitutions by replacing regex named capture grouping used in WordPress
            //url/<?P<paramname>[regex]+)/further/url
            //to
            //url/{paramname}/further/url
            $substitutions = [];
            $found = preg_match_all('/\(\?P\<(.*?)\>(.*?)\)/', $url, $matches, PREG_SET_ORDER);
            if ($found && $found > 0) {
                //for each found substituion, store the given regex
                foreach ($matches as $foundSubstitution) {
                    $substitutions[$foundSubstitution[1]] = $foundSubstitution[2]; 
                }

                //replace all regex substituions with OpenAPI substitutions
                $url = preg_replace( '/\(\?P\<(.*?)\>.*?\)/', '{$1}', $url );
            }          

            $result[ $url ] = $this->generatePathItem( $spec );
        }

        return $result;
    }

    public function generatePathItem( $spec ) {
        
        $result = [];

        foreach ( $spec['endpoints'] as $endpoint ) {
            $parameters = [];

            //create parameters for all the following methods of this endpoint
            //this means, yes, currently those parameters are duplicated in the OpenAPI document
            //because we don't use refs yet. 
            foreach ( $endpoint['args'] as $argumentName => $argument ) {
                $parameters[] = [
                    'name' => $argumentName,
                    'description' => isset( $argument['description'] ) ? $argument['description'] : '',
                    'required' => isset ( $argument['required'] ) ? $argument['required'] : 'false',
                    'schema' => $this->generateArgumentSchema( $argument )
                ];
            }

            foreach ( $endpoint['methods'] as $methodName ) {

                $method = [
                    'parameters' => $parameters,
                    'responses' => [
                        '200' => ['description' => 'OK'],
                        '400' => ['description' => 'Bad Request'],
                        '404' => ['description' => 'Not Found']
                    ]
                ];

                //if a schema is defined for the reponse of the current route add it.
                if ( isset( $spec['schema'] ) && !empty( $spec['schema'] )) {

                    $method['responses']['200']['content'] = $this->generateResponseSchema( $spec['schema'] );
                }

                //create operation object for path item with the specific method
                $result[strtolower($methodName)] = $method;
            }
        }

        return $result;
    }

    public function generateResponseSchema( $schema ) {
                    
        $schemaName = $schema['title'];

        $this->fixupRequiredFields( $schema );

        //add schema to the current schema pool to add it to the components part of the document later on.
        $this->schemaObjects[$schemaName] = $schema;

        return [
            'application/json' => [
                'schema' => [
                    '$ref' => '#/components/schemas/' . $schemaName
                ]
            ]
        ];

    }

    public function fixupRequiredFields( &$node ) {
        if ( !is_array( $node ) ) {
            return;
        }

        //get all required fields of property collections
        //and put those into a required array on the parent node
        if ( isset( $node['type'] ) && $node['type'] === 'object' && isset( $node['properties'] ) ) {
            $required = [];

            foreach( $node['properties'] as $propertyName => $property ) {
                if ( isset( $property['required'] ) && 
                    ($property['required'] === true || strtowlower($property['required']) === 'true') ) {
                    $required[] = $propertyName;
                    unset( $node['properties'][$propertyName]['required'] );
                }
            }

            if (!empty( $required )) {
                $node['required'] = $required;
            }
        }

        foreach( $node as $key => $val ) {
            if ( is_array( $val ) ) {
                $this->fixupRequiredFields( $prop );
            }
        }
    }

    public function generateArgumentSchema( $argument ) {

        $result = [
            'type' => 'string'
        ];

        //always add items if it exists, even if 'type' might not be 'array'
        if ( isset( $argument['items'] ) ) {
            $result['items'] = $argument['items'];
        }

        //always add properties if it exists, even if 'type' might not be 'object'
        if ( isset( $arguments['properties'] ) ) {
            $result['properties'] = $argument['properties'];
        }

        if ( isset( $arguments['type'] ) ) {
            $result['type'] = $argument['type'];
        }

        return $result;
    }

    public function generateSecurity() {
        return [];
    }

}