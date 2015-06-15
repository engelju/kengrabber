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

class CleanUpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("cleanup")
            ->setDescription("Clean up everything. Clear caches.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Direct access to the Container.
        /** @var Kengrabber $kg */
        $kg = $this->getApplication()->getContainer();

        $output->writeln("Cleaning up ...");
        $kg['monolog']->addDebug("START Cleaning up...");

        $kg['monolog']->addDebug("Cleaning up youtube-dl cache...");
        foreach(glob($kg['app_dir'] . "/ytdl_cache/*") as $file) {
            $kg['monolog']->addDebug("Removing file: " . $file);
            unlink($file);
        }

        $kg['monolog']->addDebug("Cleaning up unfinished parts in web/media...");
        foreach(glob($kg['web_dir'] . "/media/*.part") as $file) {
            $kg['monolog']->addDebug("Removing file: " . $file);
            unlink($file);
        }
        foreach(glob($kg['web_dir'] . "/media/*.temp.*") as $file) {
            $kg['monolog']->addDebug("Removing file: " . $file);
            unlink($file);
        }

        $kg['monolog']->addInfo("END Cleaning up...");
        $output->writeln("<info>Finished cleaning up...</info>");
    }
}