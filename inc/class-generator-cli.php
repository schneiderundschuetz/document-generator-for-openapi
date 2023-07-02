<?php 

namespace OpenAPIGenerator;
require_once __DIR__ . '/cli/class-document-generator-command.php';

class GeneratorCLI {
	
	private static $instance = null;
	
	public static function get_instance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct()
	{
		\WP_CLI::add_hook( 'after_wp_load', [$this, 'add_commands'] );
	}
	
	function add_commands()
	{
		\WP_CLI::add_command('openapi-generator', \OpenAPIGenerator\cli\DocumentGenerator_Command::class);
	}
}

if (class_exists('WP_CLI')) {
	$OpenAPIGenerator_Cli = \OpenAPIGenerator\GeneratorCLI::get_instance();
}

?>