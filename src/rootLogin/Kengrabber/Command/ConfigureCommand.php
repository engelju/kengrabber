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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class ConfigureCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("configure")
            ->setDescription("Configure kengrabber.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Direct access to the Container.
        /** @var Kengrabber $kg */
        $kg = $this->getApplication()->getContainer();

        $output->writeln("Kengrabber Configuration" . PHP_EOL);

        $newconfig = array();
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        foreach($kg['config'] as $key => $value) {
            if(is_array($value)) {
                $value = implode(";", $value);
            }
            $question = new Question(sprintf('<question>%s</question> [%s]:',$key,$value), $value);
            $newvalue = $helper->ask($input,$output,$question);
            if(is_array($value)) {
                $newvalue = explode(";", $newvalue);
            }
            $newconfig[$key] = $newvalue;
        }
        var_dump($newconfig);
    }
}