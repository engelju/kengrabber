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

class VerifyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("verify")
            ->setDescription("Verify if file exits.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Direct access to the Container.
        /** @var Kengrabber $kg */
        $kg = $this->getApplication()->getContainer();

        $output->writeln("Cleaning up...");
        $kg['monolog']->addInfo("START Cleaning up...");

        $videos = $kg['video']->getVideos();
        foreach($videos as $video) {
            /** @var Video $video */
            $path = $kg['web_dir'] . "/media/" . $video->getId() . ".mp3";
            if(!file_exists($path)) {
                $kg['monolog']->addDebug("File " . $path . " does not exist. Setting download flag to false!");
                $video->setDownloaded(false);
            } else {
                $kg['monolog']->addDebug("File " . $path . " exists. Setting download flag to true!");
                $video->setDownloaded(true);
            }
            $kg['video']->saveVideo($video);
        }

        $kg['monolog']->addInfo("END Cleaning up...");
        $output->writeln("<info>Finished cleaning up...</info>");
    }
}