User guide
==========
Factorio Updater is meant to be called from the command-line. You have basically two main ways to call it.

Way 1 - self-test
-----------------
We already touched this option in the `Installation <install.html>`_, Step 5. If you call self-test, the script verifies that it works as expected.

During this self-test, the script does not download anything from the Internet **and** it does not do anything to any of your Factorio installations. Rather, it uses its own mock files to simulate "talking" to a real Factorio API and updating a real Factorio installation. Therefore, this self-test is completely safe and you may run it even offline and without any Factorio installed. It is meant really to just verify that there is no issue in the script flow that we may prevent before we decide to do the real update.

Self-test can be called like this (this expects that your current working directory is the root folder of this project where the ``fupd.php`` file is):

.. code:: sh

   # This outputs a lot of text that should end with the info
   # that all tests were successful.
   ./fupd.php --test

   # This prevents the script from "talking", so the only way
   # to find out if it was successful is its exit code.
   #   0 = success
   #   1 = error
   ./fupd.php --test --quiet

Way 2 - normal operation
------------------------
To invoke the actual check of new versions and optionally updating is with four options.

1. ``--package`` with one of these values: ``core-linux32``, ``core-linux64``, ``core-linux_headless64``, ``core-mac``, ``core-mac-arm64``, ``core-mac-x64``, ``core-win32``, ``core-win64``, ``core_expansion-linux64``, ``core_expansion-mac``, ``core_expansion-win64``.
2. ``--build`` with one of these values: ``alpha``, ``demo``, ``expansion``, ``headless``.
3. ``--stable`` with either ``stable`` or ``experimental``.
4. ``--rootdir`` with an absolute path to the root folder of a Factorio installation. Must end with \/ or \\.

Then you have two optional parameters:

1. ``--no-install`` which causes the script to just compare the latest and local versions without running an update.
2. ``--quiet`` which causes the script to not output any text.

So you can call the script e.g. like this (given that your current working directory is the root folder of this project where the ``fupd.php`` file is):

.. code:: sh

   # Check and update
   ./fupd.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/home/user/factorio"
   # Only check
   ./fupd.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/home/user/factorio" --no-install
   # Check and update, no "talking"
   ./fupd.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/home/user/factorio" --quiet
   # Only check, no "talking"
   ./fupd.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/home/user/factorio" --no-install --quiet

At least at the beginning (after installation of this script), it is recommended not to use the ``--quiet`` option, so the script can tell you if there's an issue. This option makes more sense when used in some automated solutions which can respond to the exit code of the script.
