<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\Tests;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class DefaultTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testDefault(): void
    {
        $this->assertTrue(false);
    }
}
