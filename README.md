# Factorio Updater

[![PHP QA](https://github.com/tommander/factorio-updater/actions/workflows/php.yml/badge.svg)](https://github.com/tommander/factorio-updater/actions/workflows/php.yml)

A simple PHP script that checks the local Factorio version and compares it with the latest online version. If a newer version is available, it automatically updates Factorio to that version, applying all atomic updates one by one.

It uses the [Factorio Download API](https://wiki.factorio.com/Download_API).

## Requirements

- Factorio
- PHP 7.4+
- Composer

## Installation

```sh
cd /some/folder
git clone https://github.com/tommander/factorio-updater
cd factorio-updater
composer install
```

## Usage

Before running the script, save your Factorio username and token to the `.env` file (in the same folder as `fupd.php`). You'll find both of these on your [Factorio Profile](https://factorio.com/profile).

```dotenv
FA_USERNAME="myusername"
FA_TOKEN="mysecrettoken"
```

To verify that the script is ready to rock'n'roll:

`/some/folder/factorio-updater/fupd.php --test`

If the last line printed by the program is `[INFO] All tests were successful.`, you're good to go.

Then you can run:

`/some/folder/factorio-updater/fupd.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/some/folder/factorio"`

### Parameters

```
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
```

## Documentation

*under construction*

## QA

The `composer.json` contains the custom `qa` script that runs the following checks:

- PHP CodeSniffer (PSR-12 + PHPCompatibility)
- Psalm (strict check with error level 1)
- PHPUnit
- `fupd.php --test --quiet`

This script runs on pushes/PRs to the "main" branch.

## License

See [LICENSE](LICENSE).
