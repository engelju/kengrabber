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

namespace rootLogin\Kengrabber\Worker;

use Monolog\Logger;
use rootLogin\Kengrabber\DBWrapper\VideoWrapper;
use rootLogin\Kengrabber\Entity\Video;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class DownloadWorker {

    /**
     * @var string
     */
    protected $appDir;

    /**
     * @var string
     */
    protected $webDir;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var VideoWrapper
     */
    protected $videoWrapper;

    /**
     * @var Video
     */
    protected $video;

    public function __construct($appDir, $webDir, Logger $logger, OutputInterface $output, VideoWrapper $videoWrapper, Video $video)
    {
        $this->appDir = $appDir;
        $this->webDir = $webDir;
        $this->logger = $logger;
        $this->output = $output;
        $this->videoWrapper = $videoWrapper;
        $this->video = $video;
    }

    public function run()
    {
        $this->output->writeln("Downloading video: " . $this->video->getId());
        $this->logger->addInfo("START Downloading video: " . $this->video->getId());

        if($this->video->getDownloaded()) {
            $this->output->writeln("<info>Video already downloaded... Continue...</info>");
            $this->logger->addInfo("END Skipping video: " . $this->video->getId() . " -> Already exists!");
            return;
        }

        $builder = new ProcessBuilder(
            array(
                'youtube-dl',
                '-x',
                '--audio-format','mp3',
                '--output', $this->webDir . "/media/" . "%(id)s.%(ext)s",
                '--cache-dir', $this->appDir . "/ytdl_cache/",
                'https://youtube.com/watch?v=' . $this->video->getId()
            )
        );

        $process = $builder->getProcess();
        $process->setTimeout(0);
        $logger = $this->logger;
        $output = $this->output;
        $process->run(function($type, $buffer) use ($logger, $output) {
            if (Process::ERR === $type) {
                $output->writeln("<error>$buffer</error>");
                $logger->addError("YOUTUBE-DL: " . $buffer);
            } else {
                $logger->addDebug("YOUTUBE-DL: " . $buffer);
            }
        });

        $this->logger->addDebug("Video " . $this->video->getId() . ": Exitcode " . $process->getExitCode());

        if($process->getExitCode() === 0) {
            $this->video->setDownloaded(true);
            $output->writeln("<info>Video successfully downloaded!</info>");
            $this->logger->addInfo("Video " . $this->video->getId() . ": Downloaded successfull");
        } elseif($process->getExitCode() === 127) {
            $this->video->setDownloaded(false);
            $output->writeln("<error>youtube-dl isn't installed!</error>");
            $this->logger->addError("youtube-dl isn't available!");
        } else {
            $this->video->setDownloaded(false);
            $output->writeln("<error>Video downloading error! See logs.</error>");
            $this->logger->addError("Video " . $this->video->getId() . ": Unknown error!");
        }
        $this->videoWrapper->saveVideo($this->video);
    }
}