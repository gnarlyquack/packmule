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

namespace karlnack\packmule;

const VERSION = '1.0.1';

/*
 * The autoloader is split into two components: a "private" implementation
 * class, and a "public" non-instantiable static class that wraps the
 * implementation and provides a simple API. This is done to:
 * - Permit PSR-4 compliant autoloading, thus allowing the loader to work
 *   with and be initialized from other autoloaders (e.g., Composer).
 * - Manage a singleton instance of the loader implementation, which in turn
 *   manages the mapping of namespaces to filesystem paths.
 */

final class Autoloader {
    private static $instance;

    /* Prevent instantation of this class. */
    private function __construct() {}

    public static function add($namespace, $path) {
        self::instance()->add($namespace, $path);
    }

    public static function register($prepend = true) {
        self::instance()->register($prepend);
    }

    public static function unregister() {
        if (self::$instance) {
            self::$instance->unregister();
        }
    }

    private static function instance() {
        return self::$instance
            ?: self::$instance = new Implementation(__CLASS__);
    }
}


final class Implementation {
    private $proxy = __CLASS__;
    private $namespaces = [];
    private $search;
    private $cache = [];

    public function __construct($proxy = null) {
        if ($proxy) {
            $this->proxy = $proxy;
        }
    }

    public function add($namespace, $path) {
        if (!$namespace = strtolower(trim($namespace, '\\'))) {
            throw new \Exception("$this->proxy: Adding paths for the global namespace is not supported");
        }
        $this->namespaces[$namespace]
            = rtrim($path, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . basename(str_replace('\\', DIRECTORY_SEPARATOR, $namespace));
        $this->search = null;
    }

    public function load($name) {
        $name = strtolower(substr($name, 0, strrpos($name, '\\')));
        if (!$name || isset($this->cache[$name])) {
            return;
        }
        $this->cache[$name] = true;

        if (!$this->search) {
            $namespaces = array_keys($this->namespaces);
            /* Ensure more specific namespaces are matched first. */
            rsort($namespaces);
            $this->search = sprintf(
                '~^(%s)(?:\\\\|$)~',
                implode('|', array_map([$this, 'quote_namespace'], $namespaces))
            );
        }

        if (!preg_match($this->search, $name, $match)) {
            return;
        }
        $namespace = $match[1];
        $path = $this->namespaces[$namespace];

        if ($namespace !== $name) {
            $name = substr($name, strlen($namespace));
            $path .= str_replace('\\', DIRECTORY_SEPARATOR, $name);
        }
        $path .= '.php';

        /*
         * Yes, this introduces a race condition, but this seems preferable to
         * including the file using error suppression, which will also quash
         * error messages if the file exists and has errors of its own.
         */
        if (is_file($path)) {
            include_file($path);
        }
    }

    public function register($prepend = true) {
        /*
         * spl_autoload_register() already guards against multiple
         * registrations of the same callback.
         */
        spl_autoload_register([$this, 'load'], true, $prepend);
    }

    public function unregister() {
        /*
         * spl_autoload_unregister() already handles unregistering a callback
         * that wasn't previously registered.
         */
        spl_autoload_unregister([$this, 'load']);
    }

    private function quote_namespace($namespace) {
        return preg_quote($namespace, '~');
    }
}


/*
 * Isolate included files in their own scope to prevent them from accessing
 * the internal state of the loader (e.g., '$this' or 'self').
 */
function include_file($file) {
    include_once $file;
}
