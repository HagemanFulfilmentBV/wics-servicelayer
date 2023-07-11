<?php

namespace Hageman\Wics\ServiceLayer\Tests\Feature;

use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\Models\Item;
use Hageman\Wics\ServiceLayer\ServiceLayer;
use Hageman\Wics\ServiceLayer\Tests\TestCase;

class ItemTest extends TestCase
{
    public function testCanMakeModel()
    {
        $item = new Item();
        
        $this->assertInstanceOf(Item::class, $item);
    }

    public function testCanMakeModelFromFactory()
    {
        $item = Item::factory()->make();

        $this->assertInstanceOf(Item::class, $item);

        $this->assertNotEmpty($item->toArray());
    }

    public function testCanMakeModelCollectionFromFactory()
    {
        $items = Item::factory(2)->make();

        $this->assertInstanceOf(ModelCollection::class, $items);
        
        $this->assertCount(2, $items);
    }

    public function testCanMakeModelWithComponentsFromFactory()
    {
        $items = Item::factory(2)->make();
        
        $item = Item::factory()->makeWithComponents(...$items->pluck('code')->toArray());

        $this->assertInstanceOf(Item::class, $item);
        
        $this->assertIsArray($item->components);
    }

    public function testCanSaveNewModelToServiceLayer()
    {
        $item = Item::factory()->make();

        $saved = $item->save();
        
        $this->assertSame(201, ServiceLayer::$response->code);

        $this->assertStringContainsString('(POST)', ServiceLayer::$request);

        $this->assertTrue($saved);
    }

    public function testCanCreateNewModelToServiceLayer()
    {
        $item = Item::create(Item::factory()->make()->toArray());

        $this->assertSame(201, ServiceLayer::$response->code);

        $this->assertStringContainsString('(POST)', ServiceLayer::$request);

        $this->assertNotNull($item);
    }

    public function testCanCreateManyModelsToServiceLayer()
    {
        $items = Item::create(Item::factory(2)->make()->toArray());

        $this->assertSame(201, ServiceLayer::$response->code);

        $this->assertStringContainsString('(POST)', ServiceLayer::$request);

        $this->assertNotNull($items);
    }

    public function testCanRetrieveFromServiceLayerUsingPagination()
    {
        $items = Item::paginate(1, 1);

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $items);

        $this->assertCount(1, $items->toArray());

        $this->assertInstanceOf(Item::class, $items->first());
    }

    public function testCanRetrieveFromServiceLayer()
    {
        $items = Item::list();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $items);

        $this->assertInstanceOf(Item::class, $items->first());
    }

    public function testCanRetrieveAllFromServiceLayer()
    {
        $items = Item::all();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $items);

        $this->assertInstanceOf(Item::class, $items->first());
    }

    public function testCanRetrieveFirstFromServiceLayer()
    {
        $item = Item::first();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(Item::class, $item);
    }

    public function testCanRetrieveFromServiceLayerUsingFilter()
    {
        $items = Item::where('statusCode', 30)->list();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $items);
        
        $this->assertSame(30, $items->first()?->statusCode ?? null);
    }

    public function testCanRetrieveFromServiceLayerUsingFilterWithOperator()
    {
        $items = Item::where('statusCode', 'gte', 30)->list();

        $this->assertSame(200, ServiceLayer::$response->code, ServiceLayer::$request . ' | ' . ServiceLayer::$response);

        $this->assertInstanceOf(ModelCollection::class, $items);

        $this->assertSame(30, $items->first()?->statusCode ?? null);
    }

    public function testCanUpdateFromInstance()
    {
        $item = Item::first();

        if(is_null($item)) {
            $this->assertNotNull($item);

            return;
        }

        $itemCode = $item->code;

        $newDescription = 'Desc. update ' . date('Y-m-d');

        $item->description = $newDescription;

        $item->save();

        $this->assertSame(200, ServiceLayer::$response->code);

        $item = Item::where('code', $itemCode)->first();

        $this->assertSame($newDescription, $item->description);
    }

    public function testCanUpdateUsingFilter()
    {
        $item = Item::first();

        if(is_null($item)) {
            $this->assertNotNull($item);

            return;
        }

        $itemCode = $item->code;

        $newDescription = 'Desc. update ' . date('Y-m-d');

        $affected = Item::where('code', $itemCode)->update([
            'description' => $newDescription,
        ]);

        $this->assertSame(200, ServiceLayer::$response->code, ServiceLayer::$response . ' | ' . ServiceLayer::$request);

        $item = Item::where('code', $itemCode)->first();

        $this->assertEquals(1, $affected);

        $this->assertSame($newDescription, $item->description);
    }

    public function testCanConvertEanToItemCode()
    {
        $barcode = '2000000000015';

        $item = Item::create(array_replace_recursive(Item::factory()->make()->toArray(), ['units' => [['barcode' => $barcode]]]));

        if(is_null($item)) {
            $this->assertNotNull($item);

            return;
        }

        $itemCode = Item::ean_to_item_code($barcode);
        
        $this->assertSame($item->code, $itemCode);
    }

    public function testCanConvertItemCodeToEan()
    {
        $barcode = '2000000000022';

        $item = Item::create(array_replace_recursive(Item::factory()->make()->toArray(), ['units' => [['barcode' => $barcode]]]));

        if(is_null($item)) {
            $this->assertNotNull($item);

            return;
        }

        $ean = Item::item_code_to_ean($item->code);

        $this->assertSame($barcode, $ean);
    }
}