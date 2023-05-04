<?php

namespace Hageman\Wics\ServiceLayer\Tests\Feature;

use Hageman\Wics\ServiceLayer\Requests\Stock;
use Hageman\Wics\ServiceLayer\Requests\StockDetail;
use Hageman\Wics\ServiceLayer\Requests\StockDetails;
use Hageman\Wics\ServiceLayer\Console\Output;
use Hageman\Wics\ServiceLayer\Factories\ItemFactory;
use Hageman\Wics\ServiceLayer\Tests\Traits\ClientFromPhpunitConfig;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    use ClientFromPHPUnitConfig,
        ItemFactory;
    
    const IDENTIFIER_NOT_FOUND_MESSAGE = 'Could not retrieve stock by identifier.';
    const NO_STOCK_MESSAGE = 'Make sure there are some items in stock in Wics, and try again.';

    public function testCanRetrieveStockItems()
    {
        /**
         *
         * Known bugs:
         * ===========
         * applies to: API
         * error: API response always returns empty data, while article has stock
         * impact: Can't test for NotEmpty[data] | Can't successfully test function "testCanRetrieveStockItemByIdentifier"
         * trace: "No items with stock found"
         * ------
         *
         */
        
        $stockItems = Stock::list($this->client());

        $this->assertSame(200, $stockItems->code, self::NO_STOCK_MESSAGE);
        $this->assertTrue($stockItems->success, self::NO_STOCK_MESSAGE);
        $this->assertTrue(is_a($stockItems->get(), Collection::class));
    }

    public function testCanRetrieveStockDetails()
    {
        $stockDetails = StockDetails::list($this->client());

        $this->assertSame(200, $stockDetails->code, self::NO_STOCK_MESSAGE);
        $this->assertTrue($stockDetails->success, self::NO_STOCK_MESSAGE);
        $this->assertTrue(is_a($stockDetails->get(), Collection::class));
    }
    
    public function testCanRetrieveStockItemByIdentifier()
    {
        $stockItems = Stock::list($this->client());
        
        $identifier = $stockItems->get('0.itemCode');

        if(is_null($identifier)) {
            $this->assertNotNull($identifier, self::NO_STOCK_MESSAGE);
            
            return;
        }
        
        Output::note('Using first entry of stock item list (code: ' . $identifier . ') for testing "' . __FUNCTION__ . '"');

        $stockItem = Stock::find($this->client(), $identifier);

        $this->assertSame(200, $stockItem->code, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertTrue($stockItem->success, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertNotNull($stockItem->get('itemCode'), self::IDENTIFIER_NOT_FOUND_MESSAGE);
    }
    
    public function testCanRetrieveStockDetailByIdentifier()
    {
        $stockDetails = StockDetails::list($this->client());

        $identifier = $stockDetails->get('0.itemCode');

        if(is_null($identifier)) {
            $this->assertNotNull($identifier, self::NO_STOCK_MESSAGE);
            
            return;
        }
        
        Output::note('Using first entry of stock details list (code: ' . $identifier . ') for testing "' . __FUNCTION__ . '"');

        $stockDetail = StockDetail::find($this->client(), $identifier);

        $this->assertSame(200, $stockDetail->code, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertTrue($stockDetail->success, self::IDENTIFIER_NOT_FOUND_MESSAGE);
        $this->assertNotNull($stockDetail->get('0.itemCode'), self::IDENTIFIER_NOT_FOUND_MESSAGE);
    }

    public function testCanRetrieveSerialNumbersOfStockItemByEANSSCC()
    {
        $stockDetails = StockDetails::all($this->client());

        $identifier = $stockDetails->get('0.itemCode');

        if(is_null($identifier)) {
            $this->assertNotNull($identifier, self::NO_STOCK_MESSAGE);

            return;
        }

        $eanSscc = null;
        $serialNumbers = null;
        
        foreach($stockDetails->get() as $index => $stockDetail) {
            $eanSscc = $stockDetails->get("$index.eansscc");
            
            if(!is_null($eanSscc)) {
                $serialNumbers = Stock::serial_numbers($this->client(), $eanSscc);
                
                if(!empty($serialNumbers)) {
                    $identifier = $stockDetails->get("$index.itemCode");
                    
                    break;
                }
            }
        }

        Output::note('Using entry with EANSSCC (' . $eanSscc . ') of stock details list (code: ' . $identifier . ') for testing "' . __FUNCTION__ . '"');

        $this->assertIsArray($serialNumbers, self::IDENTIFIER_NOT_FOUND_MESSAGE);
    }
}