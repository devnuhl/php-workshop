<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Class UserStateSerializerTest
 * @package PhpSchool\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserStateSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $tmpFile;

    /**
     * @var string
     */
    private $workshopName = 'My Workshop';

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    public function setUp()
    {
        $this->tmpDir = sprintf('%s/%s/%s', sys_get_temp_dir(), $this->getName(), rand(1, 100));
        $this->tmpFile = sprintf('%s/file.json', $this->tmpDir);
    }

    public function testIfDirNotExistsItIsCreated()
    {
        $this->assertFileNotExists($this->tmpDir);
        new UserStateSerializer($this->tmpFile, $this->workshopName);
        $this->assertFileExists($this->tmpDir);
    }

    public function testConstructWhenFileExists()
    {
        mkdir($this->tmpDir, 0777, true);
        touch($this->tmpFile);
        $this->assertFileExists($this->tmpFile);
        new UserStateSerializer($this->tmpFile, $this->workshopName);
    }

    public function testSerializeEmptySate()
    {
        mkdir($this->tmpDir, 0777, true);
        $serializer = new UserStateSerializer($this->tmpFile, $this->workshopName);

        $state = new UserState;

        $expected = json_encode([
            'My Workshop' => [
                'completed_exercises' => [],
                'current_exercise' => null,
            ]
        ]);

        $res = $serializer->serialize($state);
        $this->assertTrue($res > 0);
        $this->assertSame($expected, file_get_contents($this->tmpFile));
    }

    public function testSerialize()
    {
        mkdir($this->tmpDir, 0777, true);
        $serializer = new UserStateSerializer($this->tmpFile, $this->workshopName);

        $state = new UserState(['exercise1'], 'exercise2');
        $serializer->serialize($state);

        $expected = json_encode([
            'My Workshop' => [
                'completed_exercises' => ['exercise1'],
                'current_exercise' => 'exercise2',
            ]
        ]);

        $res = $serializer->serialize($state);
        $this->assertTrue($res > 0);
        $this->assertSame($expected, file_get_contents($this->tmpFile));
    }

    public function testDeserializeNonExistingFile()
    {
        mkdir($this->tmpDir, 0777, true);
        $serializer = new UserStateSerializer($this->tmpFile, $this->workshopName);
        $state = $serializer->deSerialize();
        $this->assertNull($state->getCurrentExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    public function testDeserializeEmptyFile()
    {
        mkdir($this->tmpDir, 0777, true);
        file_put_contents($this->tmpFile, '');
        $serializer = new UserStateSerializer($this->tmpFile, $this->workshopName);
        $state = $serializer->deSerialize();
        $this->assertNull($state->getCurrentExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    public function testDeserializeNonValidJson()
    {
        mkdir($this->tmpDir, 0777, true);
        file_put_contents($this->tmpFile, 'yayayayayanotjson');
        $serializer = new UserStateSerializer($this->tmpFile, $this->workshopName);
        $state = $serializer->deSerialize();
        $this->assertNull($state->getCurrentExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider deserializerProvider
     */
    public function testDeserialize(array $data, array $expected)
    {
        mkdir($this->tmpDir, 0777, true);
        file_put_contents($this->tmpFile, json_encode($data));
        $serializer = new UserStateSerializer($this->tmpFile, $this->workshopName);
        $state = $serializer->deSerialize();

        $this->assertEquals($expected['completed_exercises'], $state->getCompletedExercises());
        $this->assertEquals($expected['current_exercise'], $state->getCurrentExercise());

        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        rmdir($this->tmpDir);
    }

    public function deserializerProvider()
    {
        return [
            'empty-array' => [
                [],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'no-data-should-return-defaults' => [
                ['My Workshop' => []],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'no-current-exercise-set' => [
                ['My Workshop' => ['completed_exercises' => []]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'completed-exercise-not-array' => [
                ['My Workshop' => ['completed_exercises' => null, 'current_exercise' => null]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'invalid-completed-exercise' => [
                ['My Workshop' => ['completed_exercises' => [null], 'current_exercise' => null]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'completed-exercises-no-current-exercise' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1']]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'completed-exercise-invalid-current-exercise' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1'], 'current_exercise' => new \stdClass]],
                ['completed_exercises' => ['exercise1'], 'current_exercise' => null]
            ],
            'completed-exercise-current-null' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1'], 'current_exercise' => null]],
                ['completed_exercises' => ['exercise1'], 'current_exercise' => null]
            ],
            'completed-exercise-with-current' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1'], 'current_exercise' => 'exercise2']],
                ['completed_exercises' => ['exercise1'], 'current_exercise' => 'exercise2']
            ]
        ];
    }

    public function testLegacyFileFormatWillBeBackedUpWithProgressReset()
    {
        $data = [
            'current_exercise' => 'Exercise 3',
            'completed_exercises' => ['Exercise 1', 'Exercise 2'],
        ];

        mkdir($this->tmpDir, 0777, true);
        file_put_contents($this->tmpFile, json_encode($data));

        $this->assertFileExists($this->tmpFile);
        $this->assertFileNotExists($this->tmpFile . '.bck');

        $serializer = new UserStateSerializer($this->tmpFile, $this->workshopName);
        $state = $serializer->deSerialize();

        $this->assertEquals([], $state->getCompletedExercises());
        $this->assertEquals(null, $state->getCurrentExercise());

        $this->assertFileNotExists($this->tmpFile);
        $this->assertFileExists($this->tmpFile . '.bck');

        $this->assertEquals($data, json_decode(file_get_contents($this->tmpFile . '.bck'), true));
        unlink($this->tmpFile . '.bck');
    }

    public function tearDown()
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }

        if (file_exists($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
    }
}
