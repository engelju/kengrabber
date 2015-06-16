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

namespace rootLogin\Kengrabber\Entity;

class Video {
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \DateTime
     */
    protected $published;

    /**
     * @var boolean
     */
    protected $downloaded = false;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $this->utf8ize($id);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $this->utf8ize($description);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $this->utf8ize($title);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param \DateTime $published
     * @return $this
     */
    public function setPublished(\DateTime $published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDownloaded()
    {
        return $this->downloaded;
    }

    /**
     * @param boolean $downloaded
     * @return $this
     */
    public function setDownloaded($downloaded)
    {
        $this->downloaded = (bool) $downloaded;

        return $this;
    }

    protected function utf8ize($s)
    {
        return $s;
        return mb_convert_encoding($s, "UTF-8", "auto");
    }
}