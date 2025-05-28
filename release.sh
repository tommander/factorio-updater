#!/bin/sh

cat fupd_begin src/FactorioUpdater.php fupd_end > fupd.php
sed -i 's/class FactorioUpdater/class FactorioUpdaterScript/g' fupd.php