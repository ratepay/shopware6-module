<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit54cf7a9b5e4c1fb6946a8a312ec6109e
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RatePay\\RatePayPayments\\' => 24,
            'RatePAY\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RatePay\\RatePayPayments\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'RatePAY\\' => 
        array (
            0 => __DIR__ . '/..' . '/ratepay/php-library/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit54cf7a9b5e4c1fb6946a8a312ec6109e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit54cf7a9b5e4c1fb6946a8a312ec6109e::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
