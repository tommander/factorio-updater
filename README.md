# Factorio Updater

Checks the local Factorio version and compares it to the latest online version. If a newer version is available, it automatically updates Factorio to that version, applying all atomic updates one by one.

## Requirements

- PHP 8.2
- Factorio

## Installation

```sh
cd /some/folder
git clone https://github.com/tommander/factorio-updater
cd factorio-updater
```

## Usage

Before running the script, save your Factorio API username and token to the following environment variables:

```sh
export FAUPSC_USER='somerandomusername'
export FAUPSC_TOKEN='mysecrettoken'
```

Beware that your username/token might be exposed in the shell history (e.g. `~/.bash_history`).

Then you can run:

`/some/folder/factorio-updater/factorio_update.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/some/folder/factorio"`

### Parameters

```
--package   Required. One of:
            core-linux32, core-linux64, core-linux_headless64,
            core-mac, core-mac-arm64, core-mac-x64, core-win32,
            core-win64, core_expansion-linux64,
            core_expansion-mac, core_expansion-win64

--build     Required. One of:
            alpha, demo, expansion, headless
--stable    Required. One of:
            stable, experimental
--rootdir   Required. Absolute path to the root folder of
            a Factorio installation.
```

### License

See [LICENSE](LICENSE).
