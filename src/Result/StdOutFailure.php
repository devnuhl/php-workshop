<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * A failure result representing the situation where the output of a solution does not match
 * that of the expected output.
 *
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutFailure implements FailureInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expectedOutput;

    /**
     * @var string
     */
    private $actualOutput;

    /**
     * @var string
     */
    private $warnings;

    /**
     * @param string $name The name of the check that produced this result.
     * @param string $expectedOutput The expected output.
     * @param string $actualOutput The actual output.
     * @param string|null $warnings
     */
    public function __construct($name, $expectedOutput, $actualOutput, $warnings = null)
    {
        $this->name             = $name;
        $this->expectedOutput   = $expectedOutput;
        $this->actualOutput     = $actualOutput;
        $this->warnings         = $warnings;
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param string $name The name of the check that produced this result.
     * @param string $expectedOutput The expected output.
     * @param string $actualOutput The actual output.
     * @return static The result.
     */
    public static function fromNameAndOutput($name, $expectedOutput, $actualOutput)
    {
        return new static($name, $expectedOutput, $actualOutput);
    }

    /**
     * @param string $name
     * @param string $expectedOutput
     * @param string $actualOutput
     * @param string $warnings
     * @return static
     */
    public static function fromNameAndWarnings($name, $expectedOutput, $actualOutput, $warnings)
    {
        return new static($name, $expectedOutput, $actualOutput, $warnings);
    }

    /**
     * Static constructor to create from an instance of `PhpSchool\PhpWorkshop\Check\CheckInterface`.
     *
     * @param CheckInterface $check The check instance.
     * @param string $expectedOutput The expected output.
     * @param string $actualOutput The actual output.
     * @return static The result.
     */
    public static function fromCheckAndOutput(CheckInterface $check, $expectedOutput, $actualOutput)
    {
        return new static($check->getName(), $expectedOutput, $actualOutput);
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
    
    /**
     * Get the expected output.
     *
     * @return string
     */
    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    /**
     * Get the actual output.
     *
     * @return string
     */
    public function getActualOutput()
    {
        return $this->actualOutput;
    }

    /**
     * @return string
     */
    public function getWarnings()
    {
        return $this->warnings;
    }
}
