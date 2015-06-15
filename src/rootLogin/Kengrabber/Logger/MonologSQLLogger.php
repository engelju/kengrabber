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

namespace rootLogin\Kengrabber\Logger;

use Monolog\Logger;
use Doctrine\DBAL\Logging\SQLLogger;

class MonologSQLLogger implements SQLLogger {

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var float
     */
    protected $startTime;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->logger->addDebug($sql);
        if ($params) {
            $this->logger->addDebug(json_encode($params));
        }
        if ($types) {
            $this->logger->addDebug(json_encode($types));
        }

        $this->startTime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $ms = round(((microtime(true) - $this->startTime) * 1000));
        $this->logger->addDebug("Query took {$ms}ms.");
    }
}