Installation
============
This installation guide shows command-line installation of the project (for both users and contributors).

Prerequisites:

1. `php 7.4+ <https://php.net>`_ because this is a PHP project.

2. `git <https://git-scm.com/>`_ for retrieving the repo.

3. `composer 2.2+ <https://getcomposer.org/>`_ for dependency management.

4. `Sphinx <https://www.sphinx-doc.org/en/master/>`_ for final documentation building.

Step 1 - Find Installation Folder
---------------------------------
Find some installation folder, where a new subfolder will be created. It does not have to be somewhere "around" your Factorio installation, but it has to be a folder which is writable for your current local user.

.. code:: sh

   cd /some/path

Step 2 - Clone GitHub Repo
--------------------------
First option is to clone the repository into a subfolder, say, ``factorio-updater``. Cloning the repo is the recommended way, as it makes it easier for you to update and optionally contribute to the project.

.. code:: sh

   git clone https://github.com/tommander/factorio-updater.git

Alternatively, you can download the source code from GitHub manually to the installation folder.

Just remember that you will have to download and unzip the code manually anytime you want to update it. Your changes to the code will not be tracked, so by each update, you may lose your changes.

.. code:: sh

   curl "https://github.com/tommander/factorio-updater/archive/refs/heads/main.zip" -o "factorio-updater-main.zip"
   unzip factorio-updater-main.zip

Step 3 - Move To the New Folder
-------------------------------
Move to the newly created folder.

.. code:: sh

   cd factorio-updater

Step 4 - Install Dependencies
-----------------------------
Install dependencies needed for the app to run. If you do not plan to contribute to our project, you might want to use the second to leave out the dev dependencies.

.. code:: sh

   # Either including development dependencies...
   composer install

   # ... or without development dependencies
   composer install --no-dev

Step 5 - Test Build (optional)
------------------------------
Now the script should be ready to use, but if you want to make sure, you can have the script verify itself.

.. code:: sh

   ./fupd.php --test

The last line of the script's output should say ``[INFO] All tests were successful.``. If so, the script is ready to serve you.

Step 6 - Secrets (optional)
---------------------------
Now if you plan to use the script for an actual updating, you need two secrets in the file ``.env`` in the root folder of the projects (i.e. where ``fupd.php`` is), otherwise Factorio API won't serve you download links for updates. Even if you plan to always run Factorio Updater with ``--no-install``, these secrets must still be present.

The content of the file may look like this:

.. code::

   FA_USERNAME="your-factorio-username"
   FA_TOKEN="your-factiorio-token"

Even if you contribute to the project, file ``.env`` is ignored and won't therefore be pushed to the repo.

Also, in your own interest, check the ``.env`` file permissions; since these are your secrets, it should be ideally only you (read-write) and the Factorio Updater (read) who can access this file.
