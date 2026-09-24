<?php declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Bootstrap;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Filesystem\Path;

class Snippets extends AbstractBootstrap
{

    private const SNIPPET_QUERY = 'INSERT INTO snippet(id, translation_key, value, author, snippet_set_id, created_at) VALUES (?, ?, ?, ?, (SELECT id FROM snippet_set WHERE iso = ?), NOW()) ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(created_at)';

    private Connection $connection;

    public function injectServices(): void
    {
        /* @phpstan-ignore-next-line */
        $this->connection = $this->container->get(Connection::class);
    }

    public function install(): void
    {
    }

    public function postInstall(): void
    {
        $this->importLocalizations();
    }


    public function update(): void
    {
    }

    public function postUpdate(): void
    {
        $this->importLocalizations();
    }

    public function uninstall(bool $keepUserData = false): void
    {
    }

    public function activate(): void
    {
    }

    public function deactivate(): void
    {
    }

    private function importLocalizations(): void
    {
        // Import localisations in ratepay/php-library as snippets
        foreach (['de' => 'de-DE', 'en' => 'en-GB'] as $rpLocale => $swLocale) {
            $filePath = Path::join(dirname(__FILE__, 6), 'vendor', 'ratepay', 'php-library', 'src', 'locales', $rpLocale . '.php');
            if (file_exists($filePath)) {
                $localizations = require $filePath;
                foreach ($localizations as $translationKey => $value) {
                    $translationKey = str_replace('_', '.', $translationKey);
                    // Decode HTML entities as otherwise we'd have to output raw snippets
                    $value = html_entity_decode($value, encoding: 'UTF-8');
                    $this->connection->executeStatement(self::SNIPPET_QUERY, [
                        Uuid::randomBytes(),
                        $translationKey,
                        $value,
                        'Ratepay',
                        $swLocale,
                    ]);
                }
            }
        }
    }
}