<?php
/**
 * @wordpress-plugin
 * Plugin Name: Document Generator for OpenAPI
 * Description: OpenAPI (fka. Swagger) Document Generator for WordPress REST API
 * Version:     1.0.0
 * Author:      Schneider & Schuetz GmbH
 * Author URI:  https://www.schneiderundschuetz.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: document-generator-for-openapi
 * Domain Path: /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/inc/class-integration.php';
require_once __DIR__ . '/inc/class-rest-controller.php';
require_once __DIR__ . '/inc/class-generator.php';
require_once __DIR__ . '/inc/class-generator-3_1_0.php';

function OpenApiGenerator() {
    return \OpenAPIGenerator\Integration::instance();
}

$GLOBALS['document_generator_for_openapi_integration'] = OpenApiGenerator();