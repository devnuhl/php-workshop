<?php

namespace PhpSchool\PhpWorkshop\Solution;

/**
 * Interface SolutionInterface
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface SolutionInterface
{
    /**
     * @return string
     */
    public function getEntryPoint();

    /**
     * @return SolutionFile[]
     */
    public function getFiles();
}
