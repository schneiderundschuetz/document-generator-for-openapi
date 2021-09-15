<?php

namespace OpenAPIGenerator;

class Integration {

    protected static $instance = null;
    protected $restController = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

    protected function __construct() {
        add_action( 'init', [$this, 'init']);
        add_action( 'rest_api_init', [$this, 'register_api']);
    }

    public function init() {

    }

    public function register_api() {
        $this->restController = new RestController();
        $this->restController->register_routes();
    }

}