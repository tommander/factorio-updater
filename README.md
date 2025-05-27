# Factorio Updater

[![PHP QA](https://github.com/tommander/factorio-updater/actions/workflows/php.yml/badge.svg)](https://github.com/tommander/factorio-updater/actions/workflows/php.yml)

```
NAME
    fupd.php - Factorio Updater

SYNOPSIS
    php fupd.php [--stable STABLE] [--quiet] [--no-install] --rootdir [PATH]
    php fupd.php [-s STABLE] [-q] [-n] -r [PATH]
    php fupd.php [--quiet] --test
    php fupd.php [-q] -t

DESCRIPTION
    A simple PHP script that checks the local Factorio version and compares it
    with the latest online version. If a newer version is available, it automa-
    tically updates Factorio to that version, applying all atomic updates one
    by one.

    The script uses the Factorio Download API:
    
        https://wiki.factorio.com/Download_API

    Please note that this script is in no way sanctioned by or associated with
    Wube Software.

PREREQUISITES
    This script works on Linux x64 systems with a Factorio headless installati-
    on and installed PHP-CLI version at least 7.4.

INSTALLATION
    Download the latest Factorio Updater release:
    
        https://github.com/tommander/factorio-updater/releases/latest/fupd.zip

    Edit the file. At the end there are two lines starting with `putenv`, so r-
    eplace "abcd" with your Factorio username and "efgh" with your Factorio to-
    ken.

OPTIONS
    -n, --no-install
        Do not install a new Factorio version.

        Ignored when --test option is used.

    -q, --quiet
        Do not output any information or error.

    -r, --rootdir=PATH
        Path to the root directory of the Factorio installation that should be
        updated.
        
        Ignored when --test option is used.

    -s, --stable=STABLE
        Compare the local Factorio version with the latest "stable" or "experi-
        mental" version. These two values are the only ones allowed; any other
        value will cause an error. Default is "stable".

        Ignored when --test option is used.

    -t, --test
        Perform a test run using only resources available in the "./tests" sub-
        directory. This subdirectory must exist and must contain all expected
        files and directories to perform a meaningful test run.

        Test run does not use internet connection or overwrite any existing in-
        stallation. The "./tests" subdirectory must exist.

RETURN VALUE
    The script's exit code indicates whether its operation was successful or n-
    ot. If the --quiet option was not used, all information about what the pro-
    gram has performed can be found on the standard output (STDOUT).

    0    The update/test run was successful.

    1    There was an error during the update/test run.

    Possible errors are explained in the section ERRORS.

CONTRIBUTING
    Thank you for considering contributing to this small project! First, you m-
    ight want to initialize your working copy. I recommend forking this project
    so you can "play" around freely.

    cd /some/folder
    git clone https://github.com/you/factorio-updater
    cd factorio-updater
    composer install

    Once you are done, commit&push your changes to your fork and then you can
    open a PR targeting the original repo. Your changes will be reviewed and if
    all goes well, you will see them integrated into the project.

    For this reason it is advisable to describe the changes in the PR or in a
    linked Issue.

    Also remember to run the "composer qa" script for following checks:

    - Composer JSON check
    - PHP CodeSniffer (PSR-12 + PHPCompatibility)
    - Psalm (strict check with error level 1)
    - PHPUnit

    This composer script runs on pushes/PRs to the "main" branch and is a prer-
    equisite for an approval of a PR.

SEE ALSO
    Documentation of the source code and some additional information incl. some
    diagrams, JSON schemas etc. can be found online:

        https://github.com/tommander/factorio-updater/wiki

BUG REPORTS
    Report bugs to:

        https://github.com/tommander/factorio-updater/issues

ERRORS
    Missing -r / --rootdir option.
        Call the script with the respective option with a value that is a path
        to the Factorio installation.

    <something> is not a string.
    <something> is not an array.
        A value in a JSON file does not have the expected data type. This prob-
        ably means the downloaded JSON from Factorio Download API has a differ-
        ent schema than expected. It can be caused by either a backwards-incom-
        patible change of the API, or an internet connection issue, or an MITM
        attack, or some device intentionally intercepting the communication (
        e.g. proxy, firewall, blocker etc.)

    <something> is not a valid string based on "<regex>".
        Similar to the error above. The value is a string, but it's not a corr-
        ect one.

    Rootdir "<path>" does not exist.
        The given path does not exist. It is possible that it actually does, b-
        ut the user running the script does not have sufficient permissions. C-
        heck the path and, if needed, adjust its permissions or call the script
        under a different user. Generally, try to avoid calling the script und-
        er root.

    Rootdir "<path>" not a dir.
        The given path exists, but it is not a directory. It can be caused by
        incorrectly pointing to the executable file of Factorio instead of the
        root directory of its installation, or by a typo.

    Rootdir "<path>" not readable.
        The given path exists and it is a directory, but the directory and its
        content cannot be read by the user running the script. Either adjust t-
        he directory permissions or call the script under a different user.

    Rootdir "<path>" not writable.
        The given path exists and it is a directory, but the directory and its
        content cannot be written to by the user running the script. Either ad-
        just the directory permissions or call the script under a different us-
        er.

    Rootdir "<path>" does not end with "/".
        The given path must have a trailing slash. Just call the script again
        with the path ending with "/".

    Executable file "<rootdir>/bin/x64/factorio" does not exist.
        The rootdir was found and has sufficient permissions, but it does not
        contain the Factorio executable file in the expected subdirectory. Che-
        ck again whether the path in the -r/--rootdir option is really a path
        to a Factorio installation. If so, the permissions for these subdirect-
        ories might not be correct for the current user. Either adjust the per-
        missions or run the script under a different user.

    Executable file "<rootdir>/bin/x64/factorio" is not executable
        The Factorio executable file is missing the "executable" permission fl-
        ag for the current user. Either adjust the permissions of the file or
        run the script under a different user.

    Factorio Username has an invalid value "<something>".
        The Factorio username is defined at the end of the "fupd.php" file and
        must be filled in with the correct value - it must not be empty and it
        may contain only uppercase/lowercase latin letters (a-z), digits (0-9),
        underscore or dash.

    Factorio Token has an invalid value "<something>".
        The Factorio token is also defined at the end of the "fupd.php" file a-
        nd must be filled in with the correct value - it must be exactly 30 ch-
        aracters long and can contain only digits (0-9) and lowercase letters
        a-f.

    File "<path>" does not exist.
        The given file does not exist. This can happen during a test run and i-
        t most probably means that the content of the "./tests" directory is n-
        ot as expected. Compare the local content with the repository and, if
        necessary, download or fix the content of the directory.

    Cannot fetch JSON from "<url>".
        The given URL does not work. This can happen during a real run of the
        script and most probably it means that either you have an issue with y-
        our internet connection or the Factorio Download API is down. Check and
        try again later.

    Cannot parse JSON downloaded from "<url>".
        The downloaded stream either cannot be parsed or it is not an array af-
        ter parsing it from JSON. You can see the raw downloaded content under
        this message, surrounded by <something> and </something> on separate l-
        ines. This can happen when there is some other device between yours an-
        d the Factorio Download API server, either intentionally (proxy server,
        firewall, blocker etc.) or not (MITM attack). If neither is the case,
        it is also possible that the API responds with an unexpected content.
        In such case, you might want to try again later.

    <json> does not contain the key "stable".
    <json> does not contain the key "experimental".
        The downloaded file with latest releases of Factorio does not contain
        the stable flag you requested. This may happen due to some changes in 
        the API or because you managed to request a stable flag that is unsupp-
        orted.

    <json> key "stable" is not an array.
    <json> key "experimental" is not an array.
    <json> key "stable" does not contain the key "headless".
    <json> key "experimental" does not contain the key "headless".
        The downloaded file with latest releases of Factorio cannot find the v-
        ersion with the requested stable flag, because the schema of the JSON
        is unexpected. This may happen due to some changes in the API or becau-
        se the file was changed/damaged. Try again in a while.

    The output of the program does not contain a version string.
    Unsupported distro "<something>".
    Unsupported build "<something>".
        The script tried to execute the Factorio executable file, but its outp-
        ut was unexpected. The command for this is "factorio --version". It can
        happen when you have some unsupported Factorio version or because the
        executable file is damaged. Try to check if it was changed by something
        or somebody. If not, ensure that you are trying to update a headless b-
        uild (64bit) for Linux.

    <json> does not contain the key "core-linux_headless64".
        The downloaded file with a list of existing atomic Factorio upgrades d-
        oes not contain the list for a 64bit headless build. This may happen d-
        ue to some changes in the API or because the downloaded file was chang-
        ed or damaged. Try again a bit later.

    <json> key "core-linux_headless64" is not an array.
        The downloaded file with a list of existing atomic Factorio upgrades c-
        ontains the list for a 64bit headless build in an unexpected format. T-
        his may happen due to some changes in the API or because the downloade-
        d file was changed or damaged. Try again a bit later.
    
    <json> key "core-linux_headless64" does not contain a sequence of updates
    from "<version>" to "<version>".
        The downloaded file with a list of existing atomic Factorio upgrades d-
        oes not contain all upgrades that will eventually upgrade Factorio to
        the latest release. This may happen due to some missing upgrades, or b-
        ecause your local version is very old, or the downloaded list was chan-
        ged or damaged.
    
    Update link is not a non-empty array.
    Update link's first item is not a string.
    Update link's first item does not start with "<something>".
    The downloaded update binary is not a string.
        The downloaded file that should contain the link to an atomic upgrade
        package has an unexpected format. This may happen due to some changes
        in the API or because the downloaded file was changed or damaged. Try
        again a bit later. If this happens during a test, it may be caused by
        a damaged content of some file in the "./tests" directory. Try to reve-
        rt or fix some potential changes.

    Update file "<path>" does not exist.
        The downloaded atomic upgrade was saved, but the target file could not
        be found. This may happen due to insufficient permissions in the targe-
        t directory, full storage or some other software/hardware issue. Check
        whether the path is correct and whether you or the user running the sc-
        ript can save files in that path.

    Update failed.
        The downloaded atomic upgrade couldn't be applied to Factorio. Under t-
        his message is the output of Factorio that was called via the command
        "factorio --apply-changes <path-to-atomic-upgrade-file>" surrounded by
        <output> and </output> on separate lines. Check the reason in the outp-
        ut. It may be due to incomplete/damaged file, which can generally be f-
        ixed by running the script again. Your Factorio installation should not
        be damaged in such case and if some updates were applied before, the u-
        pdate will continue from the version Factorio has at the moment.

    Cannot get local version.
    Cannot get local version string.
        The script was not able to determine the version of your local Factorio
        installation. There should be another error above this message, explai-
        ning why it was not possible.

    Cannot get latest release.
        The script was not able to determine the latest version of Factorio av-
        ailable online. There should be another error above this message, expl-
        aining why it was not possible.

    Cannot get update sequence.
        The script was not able to find such a combination of atomic upgrades
        that would eventually bring the local Factorio installation to the lat-
        est version. There should be another error above this message, explain-
        ing why it was not possible.

    Cannot apply update sequence.
        The script was not able to apply all atomic upgrades to bring the loca-
        l Factorio installation to the latest version. There should be another
        error above this message, explaining why it was not possible.

    Cannot get new local version.
    Cannot get new local version string.
        The script was able to perform the upgrade, but it couldn't determine
        the current version of your local Factorio installation to check, whet-
        her the upgrade was really successful. There should be another error a-
        bove this message, explaining why it was not possible.

    Local version is "<version>", but latest release is "<version>" and they a-
    re not the same => something went wrong.
        This message can appear when all atomic upgrades were applied, but the
        local installation of Factorio is still not reporting the latest versi-
        on. Probably some upgrade was not applied correctly or the new version
        has some changes that prevent the script from correctly parsing its ve-
        sion string. Check the log of actions above this message and if needed
        file a bug report.

COPYRIGHT
    Copyright (c) 2024-2025 Tomáš "Tommander" Rajnoha.

    License MIT: https://opensource.org/license/MIT.
```
