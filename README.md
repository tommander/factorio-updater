# Factorio Updater

[![PHP QA](https://github.com/tommander/factorio-updater/actions/workflows/php.yml/badge.svg)](https://github.com/tommander/factorio-updater/actions/workflows/php.yml) [![pages-build-deployment](https://github.com/tommander/factorio-updater/actions/workflows/pages/pages-build-deployment/badge.svg?branch=gh-pages)](https://github.com/tommander/factorio-updater/actions/workflows/pages/pages-build-deployment)

A simple PHP script that checks the local Factorio version and compares it with the latest online version. If a newer version is available, it automatically updates Factorio to that version, applying all atomic updates one by one.

The script uses the [Factorio Download API](https://wiki.factorio.com/Download_API).

> [!NOTE]
> This script is in no way sanctioned by or associated with [Wube Software](https://factorio.com/game/about).

## Prerequisites

* Linux x64 system
* Factorio (headless, also x64)
* PHP-CLI 7.4+

## Installation

1. Download the file "fupd.zip" of the [latest Factorio Updater release](https://github.com/tommander/factorio-updater/releases/latest/).
2. Unzip the downloaded archive (it contains only an "fupd.php" file).
3. [Optional] Copy the file e.g. to "/usr/local/bin" using `cp fupd.php /usr/local/bin/fupd` to be able to call the script from anywhere using just `fupd`.
4. Create two environment variables "FACTORIO_USERNAME" and "FACTORIO_TOKEN" containing your Factorio username and token.
5. [Optional] Perform a self-test using `fupd -t` or `fupd --test`.

### Update

Download the `fupd.zip` and unzip it to the same folder where you have the old version.

## Usage

You can perform a self-test, which does not alter/create any file and does not use internet connection. It is just meant to make sure the script is not broken.

`fupd -t`\
`fupd --test`

For the "normal" operation mode, you must use the "-r, --rootdir" option to let the script know where Factorio is located.

`fupd -r /games/factorio/`\
`fupd --rootdir /games/factorio/`

## Command-line Options

* `-t, --test`
   * Perform a self-test and ignore other options except for "-q, --quiet"
* `-q, --quiet`
   * Do not write anything to the standard output (STDOUT)
* `-n, --no-install`
   * If there is a new version available, do not install it
* `-r PATH, --rootdir=PATH`
   * Path to the Factorio installation
* `-s FLAG, --stable=FLAG`
   * Either "stable" (default) or "experimental"

> [!TIP]
> All options have a short (one-character) form and a long form. Use whichever you want.
> If you use a short and a long form at the same time, the short option's value has a priority over the long option's value.

## Documentation

See [GitHub Pages](https://tommander.github.io/factorio-updater/).

## Contributing

Thank you for considering contributing to this small project!

You will find some basic info below, the rest can be found on [GitHub Pages](https://tommander.github.io/factorio-updater/).

### Bugs, Issues, Feature Requests

Please file them in [GitHub Issues](https://github.com/tommander/factorio-updater/issues).

### Development

First, you might want to initialize your working copy. I recommend forking this project so you can "play" around freely.

```sh
cd /some/folder
git clone https://github.com/you/factorio-updater
cd factorio-updater
composer install
```

Once you are done, commit and push your changes to your fork and then you can open a PR targeting the original repo. Your changes will be reviewed and if all goes well, you will see them integrated into the project.

For this reason it is advisable to describe the changes in the PR or in a linked Issue, so that the review process is faster.

Also remember to run `composer qa` for following checks:

- Composer JSON check
- PHP CodeSniffer (PSR-12 + PHPCompatibility)
- Psalm (strict check with error level 1)
- PHPUnit

This composer script runs on pushes/PRs to the "main" branch and is a prerequisite for an approval of a PR.

## License

[MIT License](LICENSE).