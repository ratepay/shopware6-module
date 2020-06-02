<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ratepay\RatepayPayments\Resources\snippet\de_DE;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_de_DE implements SnippetFileInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'messages.de-DE';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/messages.de-DE.json';
    }

    /**
     * {@inheritdoc}
     */
    public function getIso(): string
    {
        return 'de-DE';
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
        return false;
    }
}
