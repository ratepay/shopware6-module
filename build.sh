mkdir -p build/
tar --exclude-from=.release_exclude  -czf build/dist.tar.gz .
rm -rf build/dist/RpayPayments
mkdir -p build/dist/RpayPayments
tar -xzf build/dist.tar.gz -C build/dist/RpayPayments
composer install --no-dev -d ./build/dist/RpayPayments
(cd ./build/dist/RpayPayments/src/Resources && yarn install --no-dev);
rm -rf build/dist.tar.gz
cd build/dist
zip -r RpayPayments.zip RpayPayments
