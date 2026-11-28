<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Customer;
use Payum\Core\Model\CustomerInterface;
use PHPUnit\Framework\TestCase;

final class CustomerTest extends TestCase
{
    public function testShouldImplementCustomerInterface(): void
    {
        $this->assertInstanceOf(CustomerInterface::class, new Customer());
    }

    public function testShouldKeepOneIdPerGateway(): void
    {
        $customer = new Customer();
        $customer->setGatewayCustomerId('stripe', 'cus_1');
        $customer->setGatewayCustomerId('mollie', 'cst_2');

        $this->assertSame('cus_1', $customer->getGatewayCustomerId('stripe'));
        $this->assertSame('cst_2', $customer->getGatewayCustomerId('mollie'));
    }

    public function testShouldReplaceAGatewaysId(): void
    {
        $customer = new Customer();
        $customer->setGatewayCustomerId('stripe', 'cus_1');
        $customer->setGatewayCustomerId('stripe', 'cus_2');

        $this->assertSame('cus_2', $customer->getGatewayCustomerId('stripe'));
    }

    public function testShouldReturnNullForAnUnknownGateway(): void
    {
        $this->assertNull((new Customer())->getGatewayCustomerId('stripe'));
    }

    public function testShouldExposeItsFields(): void
    {
        $customer = new Customer();
        $customer->setCustomerId('c1');
        $customer->setEmail('a@b.c');
        $customer->setName('Ann');
        $customer->setDetails([
            'a' => 1,
        ]);

        $this->assertSame('c1', $customer->getCustomerId());
        $this->assertSame('a@b.c', $customer->getEmail());
        $this->assertSame('Ann', $customer->getName());
        $this->assertSame([
            'a' => 1,
        ], $customer->getDetails());
    }
}
