<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Represents the situation where some PHP code could not be executed successfully.
 *
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeExecutionException extends RuntimeException
{

    /**
     * @var string
     */
    private $actual;
    /**
     * @var string
     */
    private $errors;

    /**
     * CodeExecutionException constructor.
     * @param string $reason
     * @param string $actual
     * @param string $errors
     */
    public function __construct($reason, $actual = null, $errors = null)
    {
        $this->message  = $reason;
        $this->actual   = $actual;
        $this->errors   = $errors;
    }

    /**
     * Static constructor to create an instance from a failed `Symfony\Component\Process\Process` instance.
     *
     * @param Process $process The `Symfony\Component\Process\Process` instance which failed.
     * @return static
     */
    public static function fromProcess(Process $process)
    {
        $message        = "PHP Code failed to execute. Error: \n%s";
        $processOutput  = $process->getOutput();
        $processErrorOutput  = $process->getErrorOutput();
        return new static(
            sprintf($message, $processErrorOutput ?: $processOutput), $processOutput, $processErrorOutput
        );
    }

    /**
     * @return null|string
     */
    public function getActual()
    {
        return $this->actual;
    }

    /**
     * @return null|string
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
