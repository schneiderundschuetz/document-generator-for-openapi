<?php

namespace OpenAPIGenerator;

abstract class GeneratorBase {
    
    protected $routes;
    protected $namespace;

    public function __construct($namespace, $routes) {
        $this->namespace = $namespace;
        $this->routes = $routes;
    }
    
    public abstract function generateDocument();

}