<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src/',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->bootstrapFiles([
        __DIR__ . '/../../../vendor/autoload.php',
    ]);

    $rectorConfig->skip([
        SwitchNegatedTernaryRector::class,
        DisallowedEmptyRuleFixerRector::class,
        \Rector\Php81\Rector\Array_\FirstClassCallableRector::class,
        ExplicitBoolCompareRector::class,
    ]);

    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(ExplicitBoolCompareRector::class);

    $rectorConfig->importNames(true, true);

    // define sets of rules
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,
        SetList::CODING_STYLE,
        LevelSetList::UP_TO_PHP_81,
    ]);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
};
