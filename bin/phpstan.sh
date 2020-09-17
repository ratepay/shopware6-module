#!/usr/bin/env bash

BASEDIR=$(cd `dirname $0` && pwd)
PLUGIN_DIR=$(dirname "$BASEDIR")
SHOPWARE_DIR=$(dirname "$PLUGIN_DIR")/../../

"$SHOPWARE_DIR"/vendor/bin/phpstan analyse -c "$PLUGIN_DIR"/phpstan.neon;

