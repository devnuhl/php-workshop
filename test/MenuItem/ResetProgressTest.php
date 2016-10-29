<?php

namespace PhpSchool\PhpWorkshopTest\MenuItem;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use PHPUnit_Framework_TestCase;

/**
 * Class ResetProgressTest
 * @package PhpSchool\PhpWorkshopTest\MenuItem
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResetProgressTest extends PHPUnit_Framework_TestCase
{
    public function testResetProgress()
    {
        $menu = $this->createMock(CliMenu::class);
        $userStateSerializer = $this->createMock(UserStateSerializer::class);
        $userStateSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf(UserState::class));
        
        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects($this->once())
            ->method('writeLine')
            ->with(("Status Reset!"));
        
        $resetProgress = new ResetProgress($userStateSerializer, $output);
        $resetProgress->__invoke($menu);
    }
}
