<?php

namespace OpenAPIGenerator;

abstract class GeneratorBase {
    
    public $routes;

    public function __construct($namespace, $routes) {
        $this->namespace = $namespace;
        $this->routes = $routes;
    }
    
    public abstract function generateDocument();

}