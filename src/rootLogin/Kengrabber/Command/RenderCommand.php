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
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RenderCommand extends Command
{
    /**
     * @var Kengrabber
     */
    protected $kg;

    protected function configure()
    {
        $this
            ->setName("render")
            ->setDescription("Render the page.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Direct access to the Container.
        /** @var Kengrabber $kg */
        $this->kg = $kg = $this->getApplication()->getContainer();

        $output->writeln("Rendering...");
        $kg['monolog']->addInfo("START Rendering...");

        $kg['monolog']->addDebug("Creating RSS Feed...");
        $this->createRSSFeed();

        $kg['monolog']->addDebug("Creating JSON file...");
        $this->createJSON();

        $kg['monolog']->addDebug("Creating HTML Overview...");
        $this->createHTMLPage();

        $kg['monolog']->addInfo("END Rendering!");
        $output->writeln("<info>Finished rendering...</info>");
    }

    protected function createRSSFeed()
    {
        $feed = new Feed();
        $channel = new Channel();
        $channel
            ->title($this->kg['option']->getOption("channel.title"))
            ->description($this->kg['option']->getOption("channel.description"))
            ->url("https://youtube.com/user/" . $this->kg['config']['youtube_channel_username'])
            ->appendTo($feed);

        $i = 0;
        foreach($this->kg['video']->getDownloadedVideos() as $video) {
            if($i >= 20) {
                break;
            }
            /** @var Video $video */
            $item = new Item();
            $item
                ->title($video->getTitle())
                ->description($video->getDescription())
                ->url("https://www.youtube.com/watch?v=" . $video->getId())
                ->enclosure($this->kg['config']['web_url'] . "/media/" . $video->getId() . ".mp3", filesize($this->kg['web_dir'] . "/media/" . $video->getId() . ".mp3"), 'audio/mpeg')
                ->appendTo($channel);
            $i++;
        }

        $rssPath = $this->kg['web_dir'] . "/podcast.rss";
        file_put_contents($rssPath, $feed, LOCK_EX);

        $this->kg['monolog']->addDebug(sprintf("Rendered %s", $rssPath));
    }

    protected function createHTMLPage()
    {
        $template = file_get_contents($this->kg['root'] . "/app/dist/web/index.html");
        $template = str_replace("%%%WEB_URL%%%", $this->kg['config']['web_url'], $template);
        file_put_contents($this->kg['web_dir'] . "/index.html", $template);

        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->kg['root'] . "/app/dist/web", \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            $destPath = $this->kg['web_dir'] . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if(file_exists($destPath)) {
                    continue;
                }
                $this->kg['monolog']->addDebug(sprintf("Creating directory %s", $destPath));
                mkdir($destPath);
            } else {
                $this->kg['monolog']->addDebug(sprintf("Copying %s to %s", $item, $destPath));
                $template = file_get_contents($item);
                $template = str_replace("%%%WEB_URL%%%", $this->kg['config']['web_url'], $template);
                file_put_contents($destPath, $template);
            }
        }

        $this->kg['monolog']->addDebug("Rendered index.html");
    }

    protected function createJSON()
    {
        $channel = new \stdClass();
        $channel->title = $this->kg['option']->getOption("channel.title");
        $desc = $channel->description = $this->kg['option']->getOption("channel.description");
        $channel->descriptionHtml = $this->generateHtml($desc);
        $channel->tracks = array();

        foreach($this->kg['video']->getDownloadedVideos() as $video) {
            /** @var Video $video */
            $v = new \stdClass();
            $v->title = $video->getTitle();
            $desc = $v->description = $video->getDescription();
            $v->descriptionHtml = $this->generateHtml($desc);
            $v->url = "media/" . $video->getId() . ".mp3";
            $v->size = filesize($this->kg['web_dir'] . "/media/" . $video->getId() . ".mp3");
            $v->youtubeUrl = "https://www.youtube.com/watch?v=" . $video->getId();
            $v->published = $video->getPublished()->getTimestamp();
            $channel->tracks[] = $v;
        }

        $json = json_encode($channel);
        if(!$json) {
            $msg = sprintf("Error generating JSON file: %s", json_last_error_msg());
            $this->kg['monolog']->addError($msg);
            return;
        }

        $jsonPath = $this->kg['web_dir'] . "/res/channel.json";
        file_put_contents($jsonPath, $json, LOCK_EX);

        $this->kg['monolog']->addDebug(sprintf("Rendered %s", $jsonPath));
    }

    protected function generateHtml($s) {
        $s = $this->makeClickableLinks($s);
        $s = str_replace("\n","<br>",$s);
        return $s;
    }

    protected function makeClickableLinks($s) {
        return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1">$1</a>', $s);
    }
}