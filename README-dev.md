# Setup for developing

1. go into the folder src/Resources
2. run `yarn install`
3. go into shopware root directory
4. run `./psh.phar administration:build`
5. run `./psh.phar storefront:build`

# Testing
Just run the script from plugin dir
`./../../../vendor/bin/phpunit`

# Build package
just call `./bin/build.sh` in the plugin dir an use the package `RpayPayments.zip` in `./build/dist/`
