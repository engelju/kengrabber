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
use rootLogin\Kengrabber\Worker\DownloadWorker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use YoutubeDl\YoutubeDl;
use YoutubeDl\Exception\CopyrightException;
use YoutubeDl\Exception\NotFoundException;
use YoutubeDl\Exception\PrivateVideoException;

class DownloadVideoListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("videolist:download")
            ->setDescription("This downloads all videos that are in the database");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Direct access to the Container.
        /** @var Kengrabber $kg */
        $kg = $this->getApplication()->getContainer();

        $output->writeln("Starting with downloading videos...");
        $kg['monolog']->addInfo("START Downloading videos");

        $videos = $kg['video']->getVideos();
        foreach($videos as $video) {
            /** @var Video $video */
            $downloadWorker = new DownloadWorker($kg['app_dir'],$kg['web_dir'],$kg['monolog'],$output,$kg['video'],$video);
            $downloadWorker->run();
        }

        $output->writeln("Finished with downloading videos...");
        $kg['monolog']->addInfo("END Downloading videos");
    }
}