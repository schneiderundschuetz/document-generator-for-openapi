<?php 

namespace Tests\Unit;
use UnitTester;

require_once 'vendor/autoload.php';
require_once '../../../wp-load.php';

/**
 * Unittest to test the whole wp/v2 namespace as a fixture
 */
class FixtureTest extends \Codeception\Test\Unit
{
    private $input;
    private $expectedResult;

    protected UnitTester $tester;

    public function _before()
    {
        $inputFile = codecept_data_dir('test-wp-v2-input.json');
        $this->input = json_decode(file_get_contents($inputFile), true);

        $resultFile = codecept_data_dir('test-wp-v2-result.json');
        $this->expectedResult = json_decode(file_get_contents($resultFile), true);
    }

    /**
     * Test the whole wp/v2 namespace as a fixture
     */
    public function testCanParseWpV2Namespace(): void
    {
        $generator = new \OpenAPIGenerator\Generator3_1_0( 'wp/v2', $this->input["routes"], false );
        $result = $generator->generateDocument();

        $this->assertEquals($this->expectedResult, $result);
    }
}