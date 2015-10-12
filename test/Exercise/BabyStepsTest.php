<?php


namespace PhpWorkshop\PhpWorkshopTest\Exercise;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exercise\BabySteps;

/**
 * Class BabyStepsTest
 * @package PhpWorkshop\PhpWorkshopTest\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BabyStepsTest extends PHPUnit_Framework_TestCase
{
    public function testHelloWorldExercise()
    {
        $e = new BabySteps;
        $this->assertEquals('Baby Steps', $e->getName());
        $this->assertEquals('Simple Addition', $e->getDescription());

        //sometime we don't get any args as number of args is random
        //we need some args for code-coverage, so just try again
        do {
            $args = $e->getArgs();
        } while (empty($args));

        foreach ($args as $arg) {
            $this->assertInternalType('int', $arg);
        }

        $this->assertFileExists(realpath($e->getSolution()));
        $this->assertFileExists(realpath($e->getProblem()));
        $this->assertNull($e->tearDown());
    }
}
