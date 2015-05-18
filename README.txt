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
used to specify the path to a module or package relative to some starting
point, which we might call the "namespace root".

Consider the following scenarios, all of which might be within a directory
called "/path/to/my/project":

file path           namespace       result
---------------     -----------     ------------------------------------
foo.php             foo             file "foo.php" is the module "foo"

foo/                foo             directory "foo" is the package "foo"

foo/bar.php         foo\bar         "bar.php" is the submodule "bar" in
                                    package "foo"

foo/bar/sna.php     foo\bar\sna     "sna.php" is the submodule "sna" in
                                     subpackage "bar" in package "foo"

In all four scenarios, "foo" is the project namespace and refers to the
file (in the case of a module) or directory (in the case of a package) that
constitutes your project's source code. The directory "/path/to/my/project"
is the namespace root: namespaces in your project identify paths relative to
this location.

The namespace root can be the same as your project's root source code
directory, but it's generally common practice (although not required) to put
the namespace root in its own directory, such as "src" or "lib". In this
case, the namespace root would then be "/path/to/my/project/src" or
"/path/to/my/project/lib", respectively.

To use Pack Mule, simply register your project namespace and its namespace
root. In all four scenarios above, the project namespace is "foo" and the
namespace root is "/path/to/my/project". Always use an absolute path when
specifying a namespace root.

NOTE: Nothing currently prevents both a module and a package of the same name
from existing side by side in the same namespace root, although doing this is
perhaps inadvisable.

Namespaces may also be prefixed with an arbitrary vendor name. There are no
limitations to this other than that the right-most namespace element must
correspond to a valid file or directory in the namespace root. So the four
scenarios above could be altered by using "vendor\foo" or "com\example\foo"
as the top-level namespace.


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
In this case, you must register your project namespace with Pack Mule in
your project's bootstrap process.

bootstrap.php:
    <?php

    // Initialize Composer's autoloader, which will in turn load Pack Mule.
    require __DIR__ . '/vendor/autoload.php';

    // Register your project namespace and its namespace root with Pack Mule.
    \karlnack\packmule\Autoloader::add('my_namespace', __DIR__);


With an autoload file
---------------------

Use this method if your project is to be used as a library in others'
projects. In this case, the client code will initialize Composer's autoloader
during their bootstrap process and your project must simply ensure its
namespace is registered with Pack Mule. This can be done by creating an
autoload file and registering the file with Composer.

Create a file "autoload.php" in your project's root directory and register
your project namespace and its namespace root with Pack Mule as follows:

autoload.php:
    <?php

    \karlnack\packmule\Autoloader::add('my_namespace', __DIR__);

Now register the autoload file with Composer by adding the following
configuration to your project's "composer.json":

    "autoload": {
        "files": ["autoload.php"]
    }

Client projects now simply need to declare a dependency on your project in
their "composer.json" and everything will work.
