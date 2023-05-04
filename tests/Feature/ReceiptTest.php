<?php

namespace Hageman\Wics\ServiceLayer\Tests\Feature;

use Hageman\Wics\ServiceLayer\Requests\Receipt;
use Hageman\Wics\ServiceLayer\Requests\Receipts;
use Hageman\Wics\ServiceLayer\Console\Output;
use Hageman\Wics\ServiceLayer\Tests\TestCase;
use Illuminate\Support\Collection;

class ReceiptTest extends TestCase
{
    const IDENTIFIER_NOT_FOUND_MESSAGE = 'Could not retrieve receipt by identifier.';
    const NO_RECEIPTS_MESSAGE = 'Make sure there are some receipts in Wics, and try again.';

    public function testCanRetrieveReceipts()
    {
        $receipts = Receipts::list($this->client());

        $this->assertSame(200, $receipts->code);
        $this->assertTrue($receipts->success);
        $this->assertTrue(is_a($receipts->get(), Collection::class));
    }

    public function testCanRetrieveReceiptByIdentifier()
    {
        $receipts = Receipts::list($this->client());

        $identifier = $receipts->get('0.number');

        if(is_null($identifier)) {
            $this->assertNotNull($identifier, self::NO_RECEIPTS_MESSAGE);

            return;
        }

        Output::note('Using first entry of receipts list (number: ' . $identifier . ') for testing "' . __FUNCTION__ . '"');

        $receipt = Receipt::find($this->client(), $identifier);

        $this->assertSame(200, $receipt->code, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertTrue($receipt->success, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertNotNull($receipt->get('reference'), self::IDENTIFIER_NOT_FOUND_MESSAGE);
    }

    public function testCanRetrieveSerialNumbersOfReceiptByIdentifier()
    {
        $receipts = Receipts::all($this->client());

        $identifier = $receipts->get('0.number');

        if(is_null($identifier)) {
            $this->assertNotNull($identifier, self::NO_RECEIPTS_MESSAGE);

            return;
        }

        $serialNumbers = null;

        foreach($receipts->get() as $index => $receipt) {
            $identifier = $receipts->get("$index.number");

            $serialNumbers = Receipt::serial_numbers($this->client(), $identifier);

            if(!empty($serialNumbers)) {
                break;
            }
        }

        Output::note('Using entry of receipts list (code: ' . $identifier . ') for testing "' . __FUNCTION__ . '"');

        $this->assertIsArray($serialNumbers, self::IDENTIFIER_NOT_FOUND_MESSAGE);
    }
}