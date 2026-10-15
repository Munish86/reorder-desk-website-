#!/bin/sh
# WP-CLI for the local site (XAMPP PHP). Hides XAMPP's harmless "Module ftp is already loaded" warning.
# MSYS_NO_PATHCONV stops Git Bash from turning arguments like '/%postname%/' into Windows paths.
DIR=$(cygpath -m "$(cd "$(dirname "$0")" && pwd)")
export MSYS_NO_PATHCONV=1
/c/xampp/php/php.exe -d display_startup_errors=0 -d log_errors=0 "$DIR/wp-cli.phar" --path=C:/xampp/htdocs/reorderdesk "$@" 2>&1 | grep -v 'Module "ftp" is already loaded'
