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

namespace rootLogin\Kengrabber\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use rootLogin\Kengrabber\DBWrapper\OptionWrapper;
use rootLogin\Kengrabber\DBWrapper\VideoWrapper;

class WrapperProvider implements ServiceProviderInterface {

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['video'] = $app->share(function() use ($app) {
           return new VideoWrapper($app['monolog'], $app['db']);
        });

        $app['option'] = $app->share(function() use ($app) {
            return new OptionWrapper($app['monolog'], $app['db']);
        });
    }

}