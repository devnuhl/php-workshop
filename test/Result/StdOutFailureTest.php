<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;

/**
 * Class StdOutFailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutFailureTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $failure = new StdOutFailure($check, 'Expected Output', 'Actual Output');
        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }
}
