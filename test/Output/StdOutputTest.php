<?php

namespace PhpSchool\PhpWorkshopTest;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Diactoros\Request;

/**
 * Class StdOutputTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutputTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Color
     */
    private $color;

    /**
     * @var StdOutput
     */
    private $output;

    public function setUp()
    {
        $this->color = new Color();
        $this->color->setForceStyle(true);
        $this->output = new StdOutput($this->color, $this->getMock(TerminalInterface::class));
    }

    public function testPrintError()
    {
        $error  = "\n";
        $error .= " [41m       [0m\n";
        $error .= " [1m[97m[41m ERROR [0m[0m[0m\n";
        $error .= " [41m       [0m\n";
        $error .= "\n";

        $this->expectOutputString($error);

        $this->output->printError('ERROR');
    }

    public function testWrite()
    {
        $message  = "There are people who actually like programming. ";
        $message .= "I don't understand why they like programming.";

        $this->expectOutputString($message);
        $this->output->write($message);
    }

    public function testWriteLine()
    {
        $message = "Talk is cheap. Show me the code.";
        $this->expectOutputString($message . "\n");
        $this->output->writeLine($message);
    }

    public function testWriteLines()
    {
        $lines = ['Line 1', 'Line 2', 'Line 3'];
        $this->expectOutputString("Line 1\nLine 2\nLine 3\n");
        $this->output->writeLines($lines);
    }

    public function testEmptyLine()
    {
        $this->expectOutputString("\n");
        $this->output->emptyLine();
    }

    public function testWriteRequestWithHeaders()
    {
        $request = (new Request('http://www.time.com/api/pt?iso=2016-01-21T18:14:33+0000'))
            ->withMethod('GET');

        $expected  = "URL:     http://www.time.com/api/pt?iso=2016-01-21T18:14:33+0000\n";
        $expected .= "METHOD:  GET\n";
        $expected .= "HEADERS: Host: www.time.com\n";

        $this->expectOutputString($expected);
        $this->output->writeRequest($request);
    }

    public function testWriteRequestWithNoHeaders()
    {
        $request = (new Request('/endpoint'))
            ->withMethod('GET');

        $expected  = "URL:     /endpoint\n";
        $expected .= "METHOD:  GET\n";

        $this->expectOutputString($expected);
        $this->output->writeRequest($request);
    }

    public function testWriteRequestWithPostBody()
    {
        $request = (new Request('/endpoint'))
            ->withMethod('POST')
            ->withHeader('Content-Type', 'application/json');

        $request->getBody()->write(
            json_encode(['data' => 'test', 'other_data' => 'test2'])
        );

        $expected  = "URL:     /endpoint\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Content-Type: application/json\n\n";
        $expected .= "BODY:{\n";
        $expected .= "    \"data\": \"test\",\n";
        $expected .= "    \"other_data\": \"test2\"\n";
        $expected .= "}\n";


        $this->expectOutputString($expected);
        $this->output->writeRequest($request);
    }
}
