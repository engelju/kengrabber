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

use Madcoda\Youtube;
use rootLogin\Kengrabber\Entity\Video;
use rootLogin\Kengrabber\Kengrabber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GrabVideoListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("videolist:grab")
            ->setDescription("This grabs a list of all videos available");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Direct access to the Container.
        /** @var Kengrabber $kg */
        $kg = $this->getApplication()->getContainer();

        $output->writeln("Starting with fetching the videos from channel: " . $kg['config']['youtube_channel_username']);

        $yt = new Youtube(array('key' => $kg['config']['youtube_api_key']));
        $channel = $yt->getChannelByName($kg['config']['youtube_channel_username']);

        // Setting channel data...
        $kg['option']->setOption('channel.title', utf8_encode($channel->snippet->title));
        $kg['option']->setOption('channel.description', utf8_encode($channel->snippet->description));

        $params = array(
            'type' => 'video',
            'channelId' => $channel->id,
            'maxResults' => 15,
            'order' => 'date',
            'safeSearch' => 'none',
            'part' => 'id, snippet'
        );

        foreach($kg['config']['youtube_queries'] as $query)
        {
            $params['q'] = $query;
            do {
                $search = $yt->searchAdvanced($params, true);

                if(is_array($search['results'])) {
                    foreach ($search['results'] as $v) {
                        $vid = $yt->getVideoInfo($v->id->videoId);

                        /** @var Video $video */
                        $video = $kg['video']->getVideoById($vid->id);
                        if($video === null) {
                            $video = new Video();
                            $video->setId($vid->id);
                        }
                        $video->setTitle(utf8_encode($vid->snippet->title));
                        $video->setDescription(utf8_encode($vid->snippet->description));
                        $video->setPublished(new \DateTime($vid->snippet->publishedAt));

                        $kg['video']->saveVideo($video);
                    }
                }

                if(isset($search['info']['nextPageToken'])) {
                    $params['pageToken'] = $search['info']['nextPageToken'];
                    $output->writeln("Go to next page...");
                } else {
                    break;
                }
            } while(true);
        }

        $output->writeln("Finished crawling...");
    }
}