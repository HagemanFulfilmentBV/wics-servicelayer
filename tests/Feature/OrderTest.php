<?php

namespace Hageman\Wics\ServiceLayer\Tests\Feature;

use Hageman\Wics\ServiceLayer\Requests\Items;
use Hageman\Wics\ServiceLayer\Requests\Order;
use Hageman\Wics\ServiceLayer\Requests\Orders;
use Hageman\Wics\ServiceLayer\Console\Output;
use Hageman\Wics\ServiceLayer\Factories\OrderFactory;
use Hageman\Wics\ServiceLayer\Tests\Traits\ClientFromPhpunitConfig;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    use ClientFromPHPUnitConfig,
        OrderFactory;

    const IDENTIFIER_NOT_FOUND_MESSAGE = 'Could not retrieve order by identifier.';
    const NO_ORDERS_MESSAGE = 'Make sure there are some orders in Wics, and try again.';

    public function testCanRetrieveOrders()
    {
        /**
         *
         * Known bugs:
         * ===========
         * applies to: API
         * error: API response always returns empty data, while there are orders in Wics
         * impact: Can't test for NotEmpty[data] | Can't successfully test function "testCanRetrieveOrders"
         * trace: "List of 0 orders"
         * ------
         *
         */
        
        $orders = Orders::list($this->client());

        $this->assertSame(200, $orders->code);
        $this->assertTrue($orders->success);
        $this->assertTrue(is_a($orders->get(), Collection::class));
        $this->assertNotEmpty($orders->get());  // Fails because of bug
    }

    public function testCanRetrieveOrderByIdentifier()
    {
        $order = new Order($this->client(), self::fake(Items::list($this->client()), 1));
        $order->create();

        if($order->code === 201) Output::note('Created new order (identifier: ' . $order->get('identifier') . ') for testing "' . __FUNCTION__ . '". Make sure to delete in test created orders from Wics.');

        $identifier = $order->get('identifier');

        $order = Order::find($this->client(), $identifier);

        $this->assertSame(200, $order->code, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertTrue($order->success, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertTrue(is_a($order->get(), Collection::class));
    }

    public function testCanRetrieveOrderSerialNumbersByIdentifier()
    {
        $order = new Order($this->client(), self::fake(Items::list($this->client()), 1));
        $order->create();

        if($order->code === 201) Output::note('Created new order (identifier: ' . $order->get('identifier') . ') for testing "' . __FUNCTION__ . '". Make sure to delete in test created orders from Wics.');

        $identifier = $order->get('identifier');

        $order = Order::serial_numbers($this->client(), $identifier);

        $this->assertSame(200, $order->code ?? 500, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertTrue($order->success, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertTrue(is_a($order->get(), Collection::class));
    }

    public function testCanInstantiateNewOrder()
    {
        $order = new Order($this->client(), self::fake(Items::list($this->client()), 2));

        $this->assertTrue($order->success);
        $this->assertTrue(is_a($order->get(), Collection::class));
        $this->assertNotEmpty($order->get());
    }

    public function testCanCreateNewOrder()
    {
        $order = new Order($this->client(), self::fake(Items::list($this->client()), 1));
        $order->create();
        
        if($order->code === 201) Output::note('Created new order (reference: ' . $order->get('reference') . ') for testing "' . __FUNCTION__ . '". Make sure to delete in test created orders from Wics.');
        
        $this->assertSame(201, $order->code);
        $this->assertTrue($order->success);
    }
}