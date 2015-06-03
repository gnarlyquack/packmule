Pack Mule
=========

Pack Mule is a PHP autoloader that takes advantage of PHP namespaces to
implement the concept of modules and packages. This means multiple classes
(along with functions and constants, although these cannot be autoloaded) can
be defined in a file and successfully autoloaded.

Pack Mule happily coexists with other autoloaders, so use it fearlessly.


Overview
========

A module is simply a file of PHP source code. A package is then simply a
directory containing one or more modules or packages. A namespace can then be
used to identify a module (or package) as follows:

1. A namespace corresponds to a file path relative to some starting point,
   called the "base directory", with namespace separators representing
   directory separators. Note that a class name, function name, constant
   name, etc., are NEVER part of a namespace.

2. The "project namespace" is an initial hierarchy of one or more names in
   the namespace and constitutes a project's top-level namespace. The project
   namespace represents the module or package that is your project and
   corresponds to a exactly one file (in the case of a module) or one
   directory (in the case of a package) in the base directory.

3. If a project namespace is longer than one name, everything except the
   rightmost name constitutes the "vendor prefix". The vendor prefix is
   excluded when mapping a namespace to a file path in the base directory.

4. The case of a namespace should match the case of the corresponding file
   path, and the file name identified by the namespace must end in ".php".

The following examples show how Pack Mule resolves fully-qualified class
names to a file name (which presumably contains the class definition).

         Class Name: \foo\Bar
  Project Namespace: foo
      Vendor Prefix:
     Base Directory: /path/to/foo/src
Resulting File Path: /path/to/foo/src/foo.php

         Class Name: \Fee\Fie\Foe
  Project Namespace: Fee\Fie
      Vendor Prefix: Fee
     Base Directory: /path/to/fie
Resulting File Path: /path/to/fie/Fie.php

         Class Name: \Acme\Logger\File\Writer
  Project Namespace: Acme\Logger
      Vendor Prefix: Acme
     Base Directory: ./acme/logger/lib
Resulting File Path: ./acme/logger/lib/Logger/File.php

         Class Name: \com\example\web\response\Status
  Project Namespace: com\example\web
      Vendor Prefix: com\example
     Base Directory: /usr/includes/example.com
Resulting File Path: /usr/includes/example.com/web/response.php

         Class Name: \framework\database\mysql\Database
  Project Namespace: framework
      Vendor Prefix:
     Base Directory: ./framework/
Resulting File Path: ./framework/framework/database/mysql.php

NOTE: Nothing currently prevents both a module and a package of the same name
from existing side by side in the same directory, although doing this is
perhaps inadvisable, and future support of this is not guaranteed.


Requirements
============

Pack Mule requires PHP 5.4 or later.


Installation
============

Use Composer to install Pack Mule by adding the following configuration to
your project's "composer.json":

    "require": {
        "karlnack/packmule": "1.0.*"
    }


Usage
=====

With a bootstrap file
---------------------

Use this method if your project is NOT to be used as a library in others'
projects and you are using Composer to manage your project's dependencies.
In this case, you must register your project namespace and its base directory
with Pack Mule in your project's bootstrap process.

bootstrap.php:
    <?php

    // Initialize Composer's autoloader, which will in turn load Pack Mule.
    require __DIR__ . '/vendor/autoload.php';

    // Register your project namespace and its base directory with Pack Mule.
    // Replace 'vendor\\project' with your actual project namespace and
    // __DIR__ with the actual base directory. Always use an absolute path
    // when specifying the base directory.
    \karlnack\packmule\Autoloader::add('vendor\\project', __DIR__);


With an autoload file
---------------------

Use this method if your project is to be used as a library in others'
projects. In this case, the client code will initialize Composer's autoloader
during their bootstrap process (which will, in turn, initialize Pack Mule)
and your project must simply ensure its namespace is registered with Pack
Mule. This can be done by using an autoload file.

Create a file "autoload.php" in your project's root directory and register
your project namespace and its base directory with Pack Mule as follows:

autoload.php:
    <?php

    // Register your project namespace and its base directory with Pack Mule.
    // Replace 'vendor\\project' with your actual project namespace and
    // __DIR__ with the actual base directory. Always use an absolute path
    // when specifying the base directory.
    \karlnack\packmule\Autoloader::add('vendor\\project', __DIR__);

Now register the autoload file with Composer by adding the following
configuration to your project's "composer.json":

    "autoload": {
        "files": ["autoload.php"]
    }

Client projects now simply need to declare a dependency on your project in
their "composer.json" and everything will work.
