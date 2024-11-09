Technical guide
===============
Here I'd like to explain a little bit how the code is structured and what is the program flow.

The main file, ``fupd.php`` parses options given to the script from the command line. Based on them, it decides what to do. There are two "cases", i.e. two decisions the script can do, apart from exiting due to an error/exception.

Script options
--------------
Here are the options:

.. code:: sh

   --package     Required. One of:
                 core-linux32, core-linux64, core-linux_headless64,
                 core-mac, core-mac-arm64, core-mac-x64, core-win32,
                 core-win64, core_expansion-linux64,
                 core_expansion-mac, core_expansion-win64
   --build       Required. One of:
                 alpha, demo, expansion, headless
   --stable      Required. One of:
                 stable, experimental
   --rootdir     Required. Absolute path to the root folder of
                 a Factorio installation.
   --no-install  Optional. Do not download/install updates if
                 a newer version exists.
   --test        Optional. Indicates a test run that does not use
                 actual Factorio installation or API, rather it
                 uses mocks in the test folder for API responses,
                 Factorio binary and update packages. This checks
                 that the script behaves as expected regarding the
                 API calls and putting together + applying the
                 update sequence with checking the output of the
                 program.
                 Using this option ignores --package, --build,
                 --stable, --rootdir and --no-install options.
   --quiet       Optional. "Do not print anything, just do your job."

Case 1 - self-test
--------------------
If the option ``--test`` is present, all other options except for ``--quiet`` are ignored, including your secrets in the ``.env`` file.

The ``--quiet`` option is good for automation scripts which basically need just the exit code of the script (either successful 0 or erroneous 1).

Self-test uses a mock Factorio binary ``tests/factroot/bin/x64/factorio`` and mock Factorio API ``tests/assets/*.json`` to just test that the scripts behaves as expected.

What it does is that for all combinations of packages (``core-linux32``, ``core-win32``, ...), build (``alpha``, ``demo``, ...) and stability (``stable``, ``experimental``) it tries to update the mock Factorio installation in ``tests/factroot`` from version "1.0.0" to the newest version ("1.1.0" for stable, "1.1.1" for experimental).

The mock Factorio API, represented by JSON files in ``tests/asssets``, has the same format and structure as the normal API. It reports hardcoded versions "1.0.0", "1.0.1", "1.1.0" and "1.1.1". There are also update packages in the same folder "1.0.0->1.0.1", "1.0.1->1.1.0" and "1.1.0->1.1.1" that the script can download and apply.

The mock Factorio binary responds to two cli options, ``--version`` and ``--apply-update <path>``. The first option causes it to output the content of the file ``tests/factroot/bin/x64/version`` to stdout, while the latter overwrites the content of the version file with the file given in the option's value.

So thanks to these mocks the script uses completely the same flow as it does during normal operation (described below) and we can ensure that the behaviour and effect of the scripts operation is as expected.

After the test is finished, successfully or not, the script exits and does not continue with any other operations.

Case 2 - normal operation
-------------------------
If the option ``--test`` is *not* present, the options above marked as "Required" really must be present, because they give the script all necessary information for downloading correct updates.

So when the ``fupd.php`` parses all options and does a low-level check of these options, it creates a new instance of the class :php:class:`TMD\\FUPD\\FactorioUpdate`, handing over all parsed options, and calls its method :php:meth:`TMD\\FUPD\\FactorioUpdate::run`.

Basically, all the important code is present in that class. The other files just contain some enums and helper functions.

The constructor of that class does additional checks of the option values given to it, so it won't continue if e.g. the Factorio root directory is not writable for the script.

If all options seem fine, it first downloads the list of latest releases (API) and compares it with the version that Factorio binary reports with ``--version``. If the version strings are the same, all is fine and the script exits.

If the version strings are different *and* the option ``--no-install`` is not present, it continues with downloading the list of all available update packages (API). Since these updates are always from one version to the version immediately following it, it might need to construct a "sequence" of updates that lead to the particular latest releases, meaning that it apply each update package until, eventually, the local version is the same as the latest one.

There might be some strange situations that the script cannot find a correct sequence - in that case it doesn't download any package and just exits with an error.

.. code::

   local version: 1.2.3
   latest release: 1.2.4
   update sequence: ["1.2.3->1.2.4"]

   local version: 1.2.3
   latest release: 2.0.0
   update sequence: ["1.2.3->1.2.4", "1.2.4->2.0.0"]

If the sequence was found successfully, it proceeds to ask the API for download links, one by one, download them, apply and continue with the next update package.

Potentially, if the script fails during this update loop, you may be left with a version that is not the latest and not your previous local version either. That allows the script to continue with the update after the error is resolved and it does not have to download some updates again, so the API has a little bit less traffic. 

Finally, if all updates have been applied, it repeats the check of the latest release and the local version. If these version strings are the same, all is fine and the script exits. Otherwise, it reports an error.

That's it, folks.
