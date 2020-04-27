<?php declare(strict_types=1);

namespace RatePay\Tests;

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
