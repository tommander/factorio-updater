# Factorio Updater

[![PHP QA](https://github.com/tommander/factorio-updater/actions/workflows/php.yml/badge.svg)](https://github.com/tommander/factorio-updater/actions/workflows/php.yml)

A simple PHP script that checks the local Factorio version and compares it with the latest online version. If a newer version is available, it automatically updates Factorio to that version, applying all atomic updates one by one.

It uses the [Factorio Download API](https://wiki.factorio.com/Download_API).

Please note that this script is in no way sanctioned by or associated with Wube Software.

## Requirements

- Linux x64
- Factorio (headless)
- PHP 7.4+
- Composer

## Installation

```sh
cd /some/folder
git clone https://github.com/tommander/factorio-updater
cd factorio-updater
composer install
```

If you do not intend to do some development or tests, you can run the last command like this:

```sh
composer install --no-dev
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

`/some/folder/factorio-updater/fupd.php --stable="stable" --rootdir="/some/folder/factorio"`

### Parameters

| Parameter | Required? | Description |
| --- | --- | --- |
| `--stable=<stable>` | Yes* | Either "stable" or "experimental" |
| `--rootdir=<path>` | Yes* | Absolute path to the root folder of a Factorio installation. |
| `--no-install` | No | Do not download/install updates if a newer version exists. |
| `--test` | No | Indicates a test run that does not use actual Factorio installation or API, rather it uses mocks in the `tests` folder for API responses, Factorio binary and update packages. This checks that the script behaves as expected regarding the API calls and putting together + applying the update sequence with checking the output of the program. Using this option ignores `--stable`, `--rootdir` and `--no-install` options. |
| `--quiet` | No | "Do not print anything, just do your job." |

\*) Required only if `--test` is *not* present.

## Documentation

Under construction, but you can check the "docs" folder in the meantime.

## QA

The `composer.json` contains the custom `qa` script that runs the following checks:

- PHP CodeSniffer (PSR-12 + PHPCompatibility)
- Psalm (strict check with error level 1)
- PHPUnit
- `fupd.php --test --quiet`

This script runs on pushes/PRs to the "main" branch.

## License

[MIT License](LICENSE).
