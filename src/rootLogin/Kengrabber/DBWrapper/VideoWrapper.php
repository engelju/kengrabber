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

namespace rootLogin\Kengrabber\DBWrapper;

use Doctrine\DBAL\Driver\Connection;
use Monolog\Logger;
use rootLogin\Kengrabber\Entity\Video;

class VideoWrapper {

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Connection
     */
    protected $db;

    public function __construct(Logger $logger, Connection $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function getVideos()
    {
        $stmt = $this->db->prepare("SELECT id, title, description, published, downloaded FROM videos");
        $stmt->execute();
        $results = $stmt->fetchAll();

        $videos = array();
        foreach($results as $res) {
            $video = new Video();
            $video->setId($res['id']);
            $video->setTitle($res['title']);
            $video->setDescription($res['description']);
            $video->setPublished(new \DateTime($res['published']));
            $video->setDownloaded((bool) $res['downloaded']);

            $videos[] = $video;
        }

        return $videos;
    }

    public function getDownloadedVideos()
    {
        $stmt = $this->db->prepare("SELECT id, title, description, published, downloaded FROM videos WHERE downloaded IS 1 ORDER BY published DESC");
        $stmt->execute();
        $results = $stmt->fetchAll();

        $videos = array();
        foreach($results as $res) {
            $video = new Video();
            $video->setId($res['id']);
            $video->setTitle($res['title']);
            $video->setDescription($res['description']);
            $video->setPublished(new \DateTime($res['published']));
            $video->setDownloaded((bool) $res['downloaded']);

            $videos[] = $video;
        }

        return $videos;
    }

    public function getVideoById($id)
    {
        $stmt = $this->db->prepare("SELECT id, title, description, published, downloaded FROM videos WHERE id IS :id");
        $stmt->bindParam("id", $id);
        $stmt->execute();

        if($res = $stmt->fetch()) {
            $video = new Video();
            $video->setId($res['id']);
            $video->setTitle($res['title']);
            $video->setDescription($res['description']);
            $video->setPublished(new \DateTime($res['published']));
            $video->setDownloaded((bool) $res['downloaded']);
            return $video;
        }

        return null;
    }

    public function saveVideo(Video $video)
    {
        $v = $this->getVideoById($video->getId());
        if($v === null) {
            $stmt = $this->db->prepare("INSERT INTO videos (id, title, description, published, downloaded) VALUES (:id, :title, :desc, :published, :downloaded)");
        } else {
            $stmt = $this->db->prepare("UPDATE videos SET title = :title, description = :desc, published = :published, downloaded = :downloaded WHERE id IS :id");
        }

        $stmt->bindParam("id", $video->getId(), \PDO::PARAM_STR);
        $stmt->bindParam("title", $video->getTitle(), \PDO::PARAM_STR);
        $stmt->bindParam("desc", $video->getDescription(), \PDO::PARAM_STR);
        $stmt->bindParam("published", $video->getPublished()->format("Y-m-d H:i:s"), \PDO::PARAM_STR);
        $downloaded = ($video->getDownloaded() == true ? 1 : 0);
        $stmt->bindParam("downloaded", $downloaded, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function deleteVideo(Video $video)
    {
        $stmt = $this->db->prepare("DELETE FROM videos WHERE id IS :id");
        $stmt->bindParam("id", $video->getId());
        $stmt->execute();
        $this->db->commit();
    }
}