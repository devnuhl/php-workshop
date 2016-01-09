<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;
use PhpSchool\PhpWorkshop\Check\CheckCollection;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\Output\OutputInterface;

/**
 * Class ExerciseDispatcher
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseDispatcher
{
    const CHECK_BEFORE = 'before';
    const CHECK_AFTER = 'after';

    /**
     * @var ExerciseRunnerInterface[]
     */
    private $runners;

    /**
     * @var CheckCollection
     */
    private $checkCollection;

    /**
     * @var CheckInterface[]
     */
    private $checksToRunBefore = [];

    /**
     * @var CheckInterface[]
     */
    private $checksToRunAfter = [];

    /**
     * @var CodePatcher
     */
    private $codePatcher;

    /**
     * Locations for composer executable
     *
     * @var array
     */
    private $composerLocations = [
        'composer',
        'composer.phar',
        '/usr/local/bin/composer',
        __DIR__ . '/../vendor/bin/composer',
    ];

    /**
     * @param ExerciseRunnerInterface[] $runners
     * @param CheckCollection $checkCollection
     * @param CodePatcher $codePatcher
     */
    public function __construct(array $runners, CheckCollection $checkCollection, CodePatcher $codePatcher)
    {
        foreach ($runners as $runner) {
            $this->registerRunner($runner);
        }
        $this->checkCollection = $checkCollection;
        $this->codePatcher = $codePatcher;
        $this->runners = $runners;
    }

    /**
     * @param ExerciseRunnerInterface $runner
     */
    private function registerRunner(ExerciseRunnerInterface $runner)
    {
        $this->runners[get_class($runner)];
    }

    /**
     * @param string $requiredCheck
     * @param $position
     * @throws CheckNotExistsException
     */
    public function requireCheck($requiredCheck, $position)
    {
        if (!$this->checkCollection->has($requiredCheck)) {
            throw new CheckNotExistsException;
        }

        switch ($position) {
            case static::CHECK_BEFORE:
                $this->checksToRunBefore[] = $this->checkCollection->getByClass($requiredCheck);
                break;
            case static::CHECK_AFTER:
                $this->checksToRunAfter[] = $this->checkCollection->getByClass($requiredCheck);
                break;
            default:
                throw InvalidArgumentException::notValidParameter(
                    'position',
                    [static::CHECK_BEFORE, static::CHECK_AFTER],
                    $position
                );
        }
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultAggregator
     * @throws CheckNotApplicableException
     * @throws ExcerciseNotConfiguredException
     */
    public function verify(ExerciseInterface $exercise, $fileName)
    {
        $this->prepareSolution($exercise->getSolution());

        $resultAggregator = new ResultAggregator;

        $this->validateChecks($this->checksToRunBefore, $exercise);
        $this->validateChecks($this->checksToRunAfter, $exercise);

        foreach ($this->checksToRunBefore as $check) {
            $resultAggregator->add($check->check($exercise, $fileName));

            if (!$resultAggregator->isSuccessful()) {
                return $resultAggregator;
            }
        }

        //patch code
        //pre-check takes care of checking that code can be parsed correctly
        //if not it would have returned already with a failure
        $originalCode = file_get_contents($fileName);
        file_put_contents($fileName, $this->codePatcher->patch($exercise, $originalCode));

        try {
            $this->getRunner($exercise)->run($exercise, $fileName, $resultAggregator);

            foreach ($this->checksToRunAfter as $check) {
                $resultAggregator->add($check->check($exercise, $fileName));
            }

            $exercise->tearDown();
        } finally {
            //put back actual code, to remove patched additions
            file_put_contents($fileName, $originalCode);
        }

        return $resultAggregator;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run(ExerciseInterface $exercise, $fileName, OutputInterface $output)
    {
        $this->prepareSolution($exercise->getSolution());
        return $this->getRunner($exercise)->run($exercise, $fileName, $output);
    }

    /**
     * @param CheckInterface[] $checks
     * @param ExerciseInterface $exercise
     * @throws CheckNotApplicableException
     * @throws ExerciseNotConfiguredException
     */
    private function validateChecks(array $checks, ExerciseInterface $exercise)
    {
        foreach ($checks as $check) {
            if (!$check->canRun($exercise->getType())) {
                throw new CheckNotApplicableException;
            }

            $checkInterface = $check->getExerciseInterface();
            if (!$exercise instanceof $checkInterface) {
                throw new ExcerciseNotConfiguredException;
            }
        }
    }

    /**
     * @param SolutionInterface $solution
     */
    private function prepareSolution(SolutionInterface $solution)
    {
        if ($solution->hasComposerFile()) {
            //prepare composer deps
            //only install if composer.lock file not available

            if (!file_exists(sprintf('%s/vendor', $solution->getBaseDirectory()))) {
                $process = new Process(
                    sprintf('%s install --no-interaction', $this->locateComposer()),
                    $solution->getBaseDirectory()
                );
                $process->run();
            }
        }
    }

    /**
     * @return string
     */
    private function locateComposer()
    {
        foreach ($this->composerLocations as $location) {
            if (file_exists($location) && is_executable($location)) {
                return $location;
            }
        }

        throw new \RuntimeException('Composer could not be located on the system');
    }

    /**
     * @param ExerciseInterface $exercise
     * @return ExerciseRunnerInterface
     */
    private function getRunner(ExerciseInterface $exercise)
    {
        $type = $exercise->getType();
        return $this->runners[$type->getValue()];
    }
}
