<?php
/**
 * Copyright 2015 Simon Erhardt <me@rootlogin.ch>
 *
 * This file is part of kengrabber.
 * kengrabber is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * kengrabber is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with kengrabber.
 * If not, see http://www.gnu.org/licenses/.
 */

namespace rootLogin\Kengrabber\Command;

use rootLogin\Kengrabber\Entity\Video;
use rootLogin\Kengrabber\Kengrabber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("build")
            ->setDescription("Build everything. Do all stuff. This command checks the defined youtube-channel, downloads, converts and build the html pages.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Direct access to the Container.
        /** @var Kengrabber $kg */
        $kg = $this->getApplication()->getContainer();

        $output->writeln("Build everything...");
        $kg['monolog']->addDebug("Build everything...");

        $commands = array(
            "videolist:grab",
            "videolist:download",
            "cleanup",
            "verify",
            "render"
        );
        
        foreach($commands as $command) {
            $builder = new ProcessBuilder(
                array(
                    "php",
                    $_SERVER["SCRIPT_FILENAME"],
                    $command
                )
            );
            if($kg['debug'] === true) {
                $builder->add("--debug");
            }

            $process = $builder->getProcess();
            $process->setTimeout(0);
            $process->run(function($type, $buffer) use ($kg, $output) {
                if (Process::ERR === $type) {
                    $output->write("<error>$buffer</error>");
                } else {
                    $output->write($buffer);
                }
            });
        }

        $output->writeln("<info>Finished building...</info>");
    }
}