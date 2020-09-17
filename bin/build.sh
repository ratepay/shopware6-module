#!/usr/bin/env bash

BASEDIR=$(cd `dirname $0` && pwd)
PLUGIN_DIR=$(dirname "$BASEDIR")
BUILD_DIR=$(dirname "PLUGIN_DIR")/build/

mkdir -p build/
tar -C "$PLUGIN_DIR"/../ --exclude-from="$BASEDIR"/.build_exclude -czf "$BUILD_DIR"/dist.tar.gz RpayPayments
rm -rf "$BUILD_DIR"/dist/RpayPayments
mkdir -p "$BUILD_DIR"/dist/RpayPayments
tar -xzf "$BUILD_DIR"/dist.tar.gz -C "$BUILD_DIR"/dist/

composer remove shopware/core shopware/administration shopware/storefront --no-install --ignore-platform-reqs -d "$BUILD_DIR"/dist/RpayPayments
composer remove --unused --ignore-platform-reqs -d "$BUILD_DIR"/dist/RpayPayments
composer install --ignore-platform-reqs --no-dev -d "$BUILD_DIR"/dist/RpayPayments

rm "$BUILD_DIR"/dist/RpayPayments/composer.json
rm "$BUILD_DIR"/dist/RpayPayments/composer.lock
cp "$PLUGIN_DIR"/composer.json "$BUILD_DIR"/dist/RpayPayments/composer.json

(cd "$BUILD_DIR"/dist/RpayPayments/src/Resources && yarn install --no-dev)
rm -rf "$BUILD_DIR"/dist.tar.gz

(cd "$BUILD_DIR"/dist && zip -r RpayPayments.zip RpayPayments)
