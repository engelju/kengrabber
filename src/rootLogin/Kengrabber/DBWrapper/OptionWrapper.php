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

class OptionWrapper {

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

    public function getOptions()
    {
        $stmt = $this->db->prepare("SELECT key, value FROM options");
        $stmt->execute();

        if($res = $stmt->fetchAll()) {
            var_dump($res); exit;
        }

        return null;
    }

    public function getOption($key)
    {
        $stmt = $this->db->prepare("SELECT value FROM options WHERE key IS :key");
        $stmt->bindParam("key", $key);
        $stmt->execute();

        if($res = $stmt->fetch()) {
            return unserialize($res['value']);
        }

        return null;
    }

    public function setOption($key, $value)
    {
        $v = $this->getOption($key);
        if($v === null) {
            $stmt = $this->db->prepare("INSERT INTO options VALUES (:key, :value)");
        } else {
            $stmt = $this->db->prepare("UPDATE options SET value = :value WHERE key IS :key");
        }

        $stmt->bindParam("key", $key, \PDO::PARAM_STR);
        $stmt->bindParam("value", serialize($value), \PDO::PARAM_STR);

        $stmt->execute();
    }

    public function deleteOption($key)
    {
        $stmt = $this->db->prepare("DELETE FROM options WHERE key IS :key");
        $stmt->bindParam("key", $key, \PDO::PARAM_STR);
        $stmt->execute();
        $this->db->commit();
    }
}