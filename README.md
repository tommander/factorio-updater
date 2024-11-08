# Factorio Updater

A simple PHP script that checks the local Factorio version and compares it with the latest online version. If a newer version is available, it automatically updates Factorio to that version, applying all atomic updates one by one.

## Requirements

- Factorio
- PHP 7.4+
- Composer

## Installation

```sh
cd /some/folder
git clone https://github.com/tommander/factorio-updater
cd factorio-updater
```

## Usage

Before running the script, save your Factorio API username and token to the `.env` file (in the same folder as `fupd.php`).

```dotenv
FA_USERNAME="myusername"
FA_TOKEN="mysecrettoken"
```

Then you can run:

`/some/folder/factorio-updater/fupd.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/some/folder/factorio"`

### Parameters

Either use these...

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
```

...or just this one.

```
--test      Indicates a test run that does not use
            actual Factorio installation or API, rather it
            uses mocks in the test folder for API responses,
            Factorio binary and update packages. This checks
            that the script behaves as expected regarding the
            API calls and putting together + applying the
            update sequence with checking the output of the
            program.
```

## QA

The `composer.json` contains the custom `qa` script that runs the following checks:

- PHP CodeSniffer (PSR-12)
- Psalm (strict check with error level 1)
- PHPUnit
- `fupd.php --test`

This script runs on pushes/PRs to the "main" branch.

## License

See [LICENSE](LICENSE).
