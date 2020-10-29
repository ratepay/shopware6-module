# Setup for developing

1. go into the folder src/Resources
2. run `yarn install`
3. go into shopware root directory
4. run `./psh.phar administration:build`
5. run `./psh.phar storefront:build`

# Testing

Please run the unit tests before committing!

1. copy the file config.php.dist to config.php
2. adjust the config.php file according the documentation (no public access. please contact your account manager.) 
3. Just run the script from plugin dir `./bin/phpunit.sh`

# Build package
just call `./bin/build.sh` in the plugin dir an use the package `RpayPayments.zip` in `./build/dist/`
