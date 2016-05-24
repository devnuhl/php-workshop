<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpParser\Error as ParseErrorException;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;

/**
 * Class Failure
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Failure implements FailureInterface
{
    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var string|null
     */
    private $expectedOutput;

    /**
     * @var string|null
     */
    private $actualOutput;

    /**
     * @var string|null
     */
    private $errors;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param string|null $reason
     * @param string|null $expectedOutput
     * @param string|null $actualOutput
     */
    public function __construct($name, $reason = null, $expectedOutput = null, $actualOutput = null, $errors = null)
    {
        $this->name           = $name;
        $this->reason         = $reason;
        $this->expectedOutput = $expectedOutput;
        $this->actualOutput   = $actualOutput;
        $this->errors         = $errors;
    }

    /**
     * @param string $name
     * @param $reason
     * @return static
     */
    public static function fromNameAndReason($name, $reason)
    {
        return new static($name, $reason);
    }
    
    /**
     * @param CheckInterface $check
     * @param string $reason
     * @return static
     */
    public static function fromCheckAndReason(CheckInterface $check, $reason)
    {
        return new static($check->getName(), $reason);
    }

    /**
     * @param string $name
     * @param CodeExecutionException $e
     * @param string|null $expectedOutput
     * @param string|null $actualOutput
     * @param string|null $errors
     * @return static
     */
    public static function fromNameAndCodeExecutionFailure(
        $name,
        CodeExecutionException $e,
        $expectedOutput = null,
        $actualOutput = null,
        $errors = null
    ) {
        return new static($name, $e->getMessage(), $expectedOutput, $actualOutput, $errors);
    }

    /**
     * @param CheckInterface $check
     * @param ParseErrorException $e
     * @param string $file
     * @return static
     */
    public static function fromCheckAndCodeParseFailure(CheckInterface $check, ParseErrorException $e, $file)
    {
        return new static(
            $check->getName(),
            sprintf('File: "%s" could not be parsed. Error: "%s"', $file, $e->getMessage())
        );
    }

    /**
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return string|null
     */
    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    /**
     * @return string|null
     */
    public function getActualOutput()
    {
        return $this->actualOutput;
    }

    /**
     * @return string|null
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
