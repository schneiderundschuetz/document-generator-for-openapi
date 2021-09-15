<?php

namespace OpenAPIGenerator;

abstract class GeneratorBase {
    
    public $wpSchema;

    public function __construct($wpSchema) {
        $this->wpSchema = $wpSchema;
    }
    
    public abstract function generateSchema();

}