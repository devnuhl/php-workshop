<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpParser\Error;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpSchool\PhpWorkshop\NodeVisitor\FunctionVisitor;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * This check verifies that the students submission contains usages of some required functions
 * and also does not use certain functions (specified by the exercise).
 *
 * @package PhpSchool\PhpWorkshop\Check
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsCheck implements SimpleCheckInterface
{
    
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Return the check's name
     *
     * @return string
     */
    public function getName()
    {
        return 'Function Requirements Check';
    }

    /**
     * Parse the students solution and check that there are usages of
     * required functions and that banned functions are not used. The requirements
     * are pulled from the exercise.
     *
     * @param ExerciseInterface $exercise The exercise to check against.
     * @param string $fileName The absolute path to the student's submission.
     * @return ResultInterface The result of the check.
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof FunctionRequirementsExerciseCheck) {
            throw new \InvalidArgumentException;
        }

        $requiredFunctions  = $exercise->getRequiredFunctions();
        $bannedFunctions    = $exercise->getBannedFunctions();

        $code = file_get_contents($fileName);

        try {
            $ast = $this->parser->parse($code);
        } catch (Error $e) {
            return Failure::fromCheckAndCodeParseFailure($this, $e, $fileName);
        }

        $visitor    = new FunctionVisitor($requiredFunctions, $bannedFunctions);
        $traverser  = new NodeTraverser;
        $traverser->addVisitor($visitor);

        $traverser->traverse($ast);

        $bannedFunctions = [];
        if ($visitor->hasUsedBannedFunctions()) {
            $bannedFunctions = array_map(function (FuncCall $node) {
                return ['function' => $node->name->__toString(), 'line' => $node->getLine()];
            }, $visitor->getBannedUsages());
        }

        $missingFunctions = [];
        if (!$visitor->hasMetFunctionRequirements()) {
            $missingFunctions = $visitor->getMissingRequirements();
        }

        if (!empty($bannedFunctions) || !empty($missingFunctions)) {
            return new FunctionRequirementsFailure($this, $bannedFunctions, $missingFunctions);
        }

        return Success::fromCheck($this);
    }

    /**
     * This check can run on any exercise type.
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getExerciseInterface()
    {
        return FunctionRequirementsExerciseCheck::class;
    }

    /**
     * This is performed after executing the student's submission because the submission may produce the correct
     * output, but do it in a way that was not correct for the task. This way the student can see the program works
     * but missed some requirements.
     *
     * @return string
     */
    public function getPosition()
    {
        return SimpleCheckInterface::CHECK_AFTER;
    }
}
