<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Resources\snippet\en_GB;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_en_GB implements SnippetFileInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'messages.en-GB';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/messages.en-GB.json';
    }

    /**
     * {@inheritdoc}
     */
    public function getIso(): string
    {
        return 'en-GB';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): string
    {
        return 'Ratepay';
    }

    /**
     * {@inheritdoc}
     */
    public function isBase(): bool
    {
        return true;
    }
}
