#!/usr/bin/env bash

BASEDIR=$(cd `dirname $0` && pwd)
PLUGIN_DIR=$(dirname "$BASEDIR")
PLUGIN_NAME=$(basename "$PLUGIN_DIR")
BUILD_DIR=$(dirname "PLUGIN_DIR")/build/

mkdir -p build/
tar -C "$PLUGIN_DIR"/../ --exclude-from="$BASEDIR"/.build_exclude -czf "$BUILD_DIR"/dist.tar.gz "$PLUGIN_NAME"
rm -rf "$BUILD_DIR"/dist/"$PLUGIN_NAME"
mkdir -p "$BUILD_DIR"/dist/"$PLUGIN_NAME"
tar -xzf "$BUILD_DIR"/dist.tar.gz -C "$BUILD_DIR"/dist/

composer remove shopware/core shopware/administration shopware/storefront --no-install --ignore-platform-reqs -d "$BUILD_DIR"/dist/"$PLUGIN_NAME"
composer remove --unused --ignore-platform-reqs -d "$BUILD_DIR"/dist/"$PLUGIN_NAME"
composer install --ignore-platform-reqs --no-dev -d "$BUILD_DIR"/dist/"$PLUGIN_NAME"

rm "$BUILD_DIR"/dist/"$PLUGIN_NAME"/composer.json
rm "$BUILD_DIR"/dist/"$PLUGIN_NAME"/composer.lock
cp "$PLUGIN_DIR"/composer.json "$BUILD_DIR"/dist/"$PLUGIN_NAME"/composer.json

(cd "$BUILD_DIR"/dist/"$PLUGIN_NAME"/src/Resources && yarn install --no-dev)

rm -rf "$BUILD_DIR"/dist/"$PLUGIN_NAME"/vendor/ratepay/php-library/tests
rm -rf "$BUILD_DIR"/dist.tar.gz

(cd "$BUILD_DIR"/dist && zip -r "$PLUGIN_NAME".zip "$PLUGIN_NAME")
