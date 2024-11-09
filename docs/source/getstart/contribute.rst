Contribute
==========
This short guide is just meant to give you some additional info in case you plan to contribute to the project.

Firstly, thank you for that, it is greatly appreciated :)

The prerequisite is that you read the `Installation <install.html>`_ and `Technical guide <techguide.html>`_, so you have some basic info about how the script works. The `API <../api/index.html>`_ is your friend for low-level information about the classes, enums, methods, properties etc.

Tech stack for development:

- Text editor
- PHP-cli 7.4+ (development is currently done on PHP 8.2; no special requirement for extensions)
- Composer 2.2+ (lower versions do not support the PHP superglobal for autoloader path -> you might have an issue with running the binary via ``composer run-script``)
- git
- python3 + python-sphinx + python3-sphinx-rtd-theme (for the documentation)
