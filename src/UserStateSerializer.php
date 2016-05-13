<?php

namespace PhpSchool\PhpWorkshop;

/**
 * Class UserStateSerializer
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserStateSerializer
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $workshopName;

    /**
     * @param string $path
     * @param string $workshopName
     */
    public function __construct($path, $workshopName)
    {
        $this->workshopName = $workshopName;
        $this->path = $path;

        if (file_exists($path)) {
            return;
        }

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
    }

    /**
     * @param UserState $state
     *
     * @return int
     */
    public function serialize(UserState $state)
    {
        if (!file_exists($this->path)) {
            $data = [];
            $data[$this->workshopName] = [
                'completed_exercises'   => $state->getCompletedExercises(),
                'current_exercise'      => $state->getCurrentExercise(),
            ];
        } else {
            $data = $this->readJson($this->path);
            $data[$this->workshopName] = [
                'completed_exercises'   => $state->getCompletedExercises(),
                'current_exercise'      => $state->getCurrentExercise(),
            ];
        }

        return file_put_contents($this->path, json_encode($data));
    }

    /**
     * @return UserState
     */
    public function deSerialize()
    {
        $json = $this->readJson($this->path);
        if (null === $json) {
            $this->wipeFile();
            return new UserState();
        }

        //if completed_exercises is set
        //then this is the legacy format
        if (isset($json['completed_exercises'])) {
            rename($this->path, sprintf('%s.bck', $this->path));
            return new UserState();
        }

        if (!isset($json[$this->workshopName])) {
            return new UserState();
        }

        $json = $json[$this->workshopName];
        if (!array_key_exists('completed_exercises', $json)) {
            return new UserState();
        }

        if (!array_key_exists('current_exercise', $json)) {
            return new UserState();
        }

        if (!is_array($json['completed_exercises'])) {
            $json['completed_exercises'] = [];
        }

        foreach ($json['completed_exercises'] as $i => $exercise) {
            if (!is_string($exercise)) {
                unset($json['completed_exercises'][$i]);
            }
        }

        if (null !== $json['current_exercise'] && !is_string($json['current_exercise'])) {
            $json['current_exercise'] = null;
        }

        return new UserState(
            $json['completed_exercises'],
            $json['current_exercise']
        );
    }

    /**
     * @param string $filePath
     * @return array|null
     */
    private function readJson($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $data = file_get_contents($filePath);

        if (trim($data) === "") {
            return null;
        }

        $data = @json_decode($data, true);

        if (null === $data && JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        return $data;
    }

    /**
     * Remove the file
     */
    private function wipeFile()
    {
        @unlink($this->path);
    }
}
