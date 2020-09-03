(cd ./src/Resources && yarn install --no-dev);
mkdir -p build/
tar --exclude-from=.release_exclude  -czf build/dist.tar.gz .
rm -rf build/dist/RatepayPayments
mkdir -p build/dist/RatepayPayments
tar -xzf build/dist.tar.gz -C build/dist/RatepayPayments
composer install --no-dev -d ./build/dist/RatepayPayments
rm -rf build/dist.tar.gz
cd build/dist
zip -r RatepayPayments.zip RatepayPayments
