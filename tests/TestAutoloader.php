<?php
/*
 * Pack Mule
 * Copyright (c) 2015 Karl Nack
 *
 * This file is subject to the license terms in the LICENSE file found in the
 * top-level directory of this distribution. No part of this project,
 * including this file, may be copied, modified, propagated, or distributed
 * except according to the terms contained in the LICENSE file.
 */

use karlnack\packmule\Autoloader;

class TestAutoloader {
    public function setup() {
        Autoloader::register();
    }

    public function teardown() {
        Autoloader::unregister();
    }

    public function test_autoload() {
        Autoloader::add('apitest', __DIR__ . '/packages/apitest');
        $class = 'apitest\\Foo';
        assert('!class_exists($class, false)');
        assert('class_exists($class)');
    }
}
