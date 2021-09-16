<?php

namespace OpenAPIGenerator;

class Generator3_1_0 extends GeneratorBase {

    protected $components = [];

    public function generateDocument() {
        return apply_filters( 'openapi_generator_v3_1', $this->generateRoot(), $this);
    }

    public function generateRoot () {
        $result = [
            'openapi' => '3.1.0',
            'info' => $this->generateInfo(),
            'jsonSchemaDialect' => 'http://json-schema.org/draft-04/schema#',
            'servers' => $this->generateServers(),
            'paths' => $this->generatePaths(),
            'security' => $this->generateSecurity()
        ];

        if ( !empty( $this->components )) {
            $result['components'] = $this->components;
        }

        return $result;
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

            $substitutions = $this->getSubstitutions( $url );

            //replace all regex substituions with OpenAPI substitutions
            $url = preg_replace( '/\(\?P\<(.*?)\>.*?\)(\/|$)/', '{$1}$2', $url );  

            $result[ $url ] = $this->generatePathItem( $spec, $substitutions );
        }

        return $result;
    }

    public function getSubstitutions( $url ) {
        //create OpenAPI style substitutions by replacing regex named capture grouping used in WordPress
        //url/<?P<paramname>[regex]+)/further/url
        //to
        //url/{paramname}/further/url

        $substitutions = [];
        $found = preg_match_all( '/\(\?P\<(.*?)\>(.*?)\)(\/|$)/', $url, $matches, PREG_SET_ORDER);
        if ($found && $found > 0) {
            //for each found substituion, store the given regex
            foreach ($matches as $foundSubstitution) {
                $substitutions[$foundSubstitution[1]] = $foundSubstitution[2]; 
            }
        }

        return $substitutions;
    }

    public function generatePathItem( $spec, $substitutions ) {
        
        $result = [];

        foreach ( $spec['endpoints'] as $endpoint ) {
            $parameters = [];

            //create parameters for all the following methods of this endpoint
            //this means, yes, currently those parameters are duplicated in the OpenAPI document
            //because we don't use refs yet. 
            foreach ( $endpoint['args'] as $argumentName => $argument ) {
                $parameters[] = $this->generateParameterObject( $argumentName, $argument, $substitutions );
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

    public function generateParameterObject( $argumentName, $argument, $substitutions ) {
        $in = \array_key_exists( $argumentName, $substitutions ) ? 'path' : 'query';
        
        $result = [
            'name' => $argumentName,
            'in' => $in,
            'description' => isset( $argument['description'] ) ? $argument['description'] : '',
            'required' => $in === 'path' ? true : (isset ( $argument['required'] ) ? $argument['required'] : false),
            'schema' => $this->generateArgumentSchema( $argumentName, $argument )
        ];

        return $result;
    }

    public function generateResponseSchema( $schema ) {
                    
        $schemaName = $schema['title'];

        $this->fixupRequiredFields( $schema );
        //$this->extractReusableSchema( $schema, $schemaName, $schemaName );

        //add schema to the current schema pool to add it to the components part of the document later on.
        if ( !isset( $this->components['schemas'] ) ) {
            $this->components['schemas'] = [];
        }
        $this->components['schemas'][$schemaName] = $this->generateSchemaObject( $schema );

        return [
            'application/json' => [
                'schema' => [
                    '$ref' => '#/components/schemas/' . $schemaName
                ]
            ]
        ];
    }

    public function generateSchemaObject( $schemaObject ) {
        $result = ['type' => 'string'];

        if ( isset( $schemaObject['type'] ) ) {
            $result['type'] = $schemaObject['type'];

            if ($schemaObject['type'] === 'object' && isset($schemaObject['properties'])) {
                foreach($schemaObject['properties'] as $key => $parameter) {
                    $result['properties'][$key] = $this->generateSchemaObject($parameter);
                }
            }

            if ($schemaObject['type'] === 'array' && isset($schemaObject['items'])) {
                foreach($schemaObject['items'] as $key => $item) {
                    $result['items'][$key] = $this->generateSchemaObject($item);
                }
            }
        }

        if (isset($schemaObject['format'])) {
            $result['format'] = $schemaObject['format'];
        }

        if (isset($schemaObject['enum'])) {
            $result['enum'] = array_values( $schemaObject['enum'] );
        }

        return $result;
    }

    public function generateArgumentSchema( $argumentName, $argument ) {

        $result = [
            'type' => 'string'
        ];

        //always add items if it exists, even if 'type' might not be 'array'
        if ( isset( $argument['items'] ) ) {
            $result['items'] = $argument['items'];
        }

        //always add properties if it exists, even if 'type' might not be 'object'
        if ( isset( $argument['properties'] ) ) {
            $result['properties'] = $argument['properties'];
        }

        if ( isset( $argument['type'] ) ) {
            $result['type'] = $argument['type'];
        }

        $this->fixupRequiredFields( $result );
        $this->extractReusableSchema( $result, $argumentName, $argumentName );

        return $result;
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
                if ( isset( $property['required'] ) ) {
                    if ( $property['required'] === true || strtowlower($property['required']) === 'true' ) {
                        $required[] = $propertyName;
                    }

                    unset( $node['properties'][$propertyName]['required'] );
                }
            }

            if (!empty( $required )) {
                $node['required'] = $required;
            }
        }

        foreach( $node as $key => $val ) {
            if ( is_array( $val ) ) {
                $this->fixupRequiredFields( $node[$key] );
            }
        }
    }

    public function extractReusableSchema( &$node, $currentKey, $context ) {
        if ( !is_array( $node ) ) {
            return;
        }

        //visit all leaves prior to changes
        foreach( $node as $key => $value ) {
            $this->extractReusableSchema( $node[$key], $key, $context );
        }
        
        //when all children are visited,
        //extract all objects
        if ( isset( $node['type'] ) &&
            $node['type'] === 'object' &&
            isset( $node['properties'] )) {
            
            $uniqueKey = $currentKey;
            if ($context !== $currentKey) {
                $uniqueKey = $context . '_' . $currentKey;
            }

            unset( $node['properties'] );
            unset( $node['type'] );
            unset( $node['items'] );
            unset( $node['context'] );
            unset( $node['readonly'] );

            $i = 1;
            $newKey = $uniqueKey;
            while ( isset( $this->components['schemas'][$newKey] ) &&
                $this->components['schemas'][$newKey] !== $node) {
                    $newKey = $uniqueKey . '_' . $i++;
            }

            $this->components['schemas'][$newKey] = $node;


            $node['$ref'] = '#/components/schemas/' . $newKey;

        }
/*


            foreach ( $node['properties'] as $propKey => $propType ) {

                if ( !isset( $propType['type'] ) || ($propType['type'] !== 'object' && $propType['type'] !== 'array') ) { 
                    continue;
                }

                $originalPropKey = $propKey;

                if ( !isset( $this->components['schemas'] ) ) {
                    $this->components['schemas'] = [];
                }

                if ( isset( $this->components['schemas'][$propKey] ) && $this->components['schemas'][$propKey] !== $propType ) {

                    //we have a clash..
                    $propKey = $context . '_' . $originalPropKey;
                    
                    $i = 1;
                    while ( isset( $this->components['schemas'][$propKey] ) &&
                            $this->components['schemas'][$propKey] !== $propType ) {
                        $propKey = $context . '_' . $originalPropKey . '_' . $i++;
                    }


                    //die(print_r($propType, true) . ' ' . print_r( $this->components['schemas'][$propKey], true));
                    //throw new \Error( 'CLASH' );
                }

                $this->components['schemas'][$propKey] = $propType;

                //find matching type
                /*foreach ( $this->components['schemas'] as $existingSchemaName => $existingType ) {
                    if ( $existingType === $propType ) {
                        $generatedSchemaKey = $existingSchemaName;
                        break;
                    }
                }

                if ( !$generatedSchemaKey ) {
                    $generatedSchemaKey = wp_generate_uuid4();
                    $propType['name'] = $propKey;
                    $this->components['schemas'][$propKey] = $propType;            
                }

                $node['properties'][$originalPropKey] = [ '$ref' => '#/components/schemas/' . $propKey ];
            }
        }*/
    }

    public function generateSecurity() {
        return [];
    }

}