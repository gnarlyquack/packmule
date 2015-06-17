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

use karlnack\packmule\Implementation;

class TestImplementation {
    private $loader;

    public function setup() {
        $this->loader = new Implementation();
        $path = __DIR__ . '/packages';

        $this->loader->add('package', "$path/package");
        $this->loader->add('vendor\\package', "$path/vendor");
        $this->loader->add('package\\subnamespace', "$path/subnamespace");
        $this->loader->add('vendor\\foo', "$path/foo");
        $this->loader->add('vendor\\foobar', "$path/foobar");
        $this->loader->add('INSENSITIVE\\PACKAGE', "$path/case-sensitivity");

        $this->loader->register();
    }

    public function teardown() {
        $this->loader->unregister();
    }

    /* helper assertions */

    private function assert_loaded($class) {
        assert('!class_exists($class, false)');
        assert('class_exists($class)');
    }

    private function assert_not_loaded($class) {
        assert('!class_exists($class)');
    }

    /* tests */

    public function test_basic_autoloading() {
        $this->assert_loaded('package\\Foo');
        $this->assert_loaded('package\\module\\Foo');
        $this->assert_loaded('package\\package\\Foo');
        $this->assert_loaded('package\\package\\module\\Foo');
        $this->assert_loaded('package\\package\\package\\Foo');
    }

    public function test_vendor() {
        $this->assert_loaded('vendor\\package\\Foo');
        $this->assert_loaded('vendor\\package\\module\\Foo');
        $this->assert_loaded('vendor\\package\\package\\Foo');
        $this->assert_loaded('vendor\\package\\package\\module\\Foo');
        $this->assert_loaded('vendor\\package\\package\\package\\Foo');
    }

    public function test_subnamespace_at_different_path() {
        $this->assert_loaded('package\\subnamespace\\Foo');
    }

    public function test_potentially_conflicting_names() {
        $this->assert_loaded('vendor\\foo\\Foo');
        $this->assert_loaded('vendor\\foobar\\Foo');
    }

    public function test_unregistered_namespace() {
        $this->assert_not_loaded('foo\\Foo');
        $this->assert_not_loaded('vendor\\foobaz\\Foo');
    }

    public function test_nonexistent_file() {
        $this->assert_not_loaded('package\\foo\\Foo');
    }

    public function test_nonexistent_class() {
        $this->assert_not_loaded('package\\module\\Bar');
    }

    public function test_loading_global_namespace() {
        $this->assert_not_loaded('package');
    }

    public function test_registering_global_namespace() {
        $e = easytest\assert_exception(
            'Exception',
            function() { $this->loader->add('', __DIR__); }
        );

        easytest\assert_identical(
            'karlnack\\packmule\\Autoloader: Adding paths for the global namespace is not supported',
            $e->getMessage()
        );
    }

    public function test_autoloading_is_case_insensitive() {
        $this->assert_loaded('Insensitive\\Package\\Module\\Foo');
    }
}
