<?php declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {

    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ]);

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, ['header' => 'Copyright (c) Ratepay GmbH

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.', 'separate' => 'bottom', 'location' => 'after_declare_strict', 'comment_type' => 'comment']);

    $ecsConfig->sets([
        SetList::CLEAN_CODE,
        SetList::COMMON,
        SetList::STRICT,
        SetList::PSR_12,
    ]);

    $ecsConfig->rules([
        PsrAutoloadingFixer::class
    ]);

    $ecsConfig->skip([
        ProtectedToPrivateFixer::class,
        NotOperatorWithSpaceFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        AssignmentInConditionSniff::class
    ]);
};
