#!/usr/bin/env bash

BASEDIR=$(dirname "$0")
PARENTIR=$(dirname "$BASEDIR")

mkdir -p build/
tar --exclude-from="$BASEDIR"/.build_exclude -czf "$BASEDIR"/dist.tar.gz "$PARENTIR"
rm -rf "$BASEDIR"/dist/RpayPayments
mkdir -p "$BASEDIR"/dist/RpayPayments
tar -xzf "$BASEDIR"/dist.tar.gz -C "$BASEDIR"/dist/RpayPayments

composer remove shopware/core shopware/administration shopware/storefront --no-install --ignore-platform-reqs -d "$BASEDIR"/dist/RpayPayments
composer remove --unused --ignore-platform-reqs -d "$BASEDIR"/dist/RpayPayments
composer install --ignore-platform-reqs --no-dev -d "$BASEDIR"/dist/RpayPayments

rm "$BASEDIR"/dist/RpayPayments/composer.json
rm "$BASEDIR"/dist/RpayPayments/composer.lock
cp "$BASEDIR"/../composer.json "$BASEDIR"/dist/RpayPayments/composer.json

(cd "$BASEDIR"/dist/RpayPayments/src/Resources && yarn install --no-dev)
rm -rf "$BASEDIR"/dist.tar.gz

(cd "$BASEDIR"/dist && zip -r RpayPayments.zip RpayPayments)
