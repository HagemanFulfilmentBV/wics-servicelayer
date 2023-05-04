<?php

namespace Hageman\Wics\ServiceLayer\Tests\Unit;

use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\Models\Item;
use Hageman\Wics\ServiceLayer\Tests\TestCase;

class ItemFactoryTest extends TestCase
{
    public function testThatFactoryReturnsModel()
    {
        $model = Item::factory()->make();

        $this->assertInstanceOf(Item::class, $model);

        $this->assertIsObject($model);

        $this->assertNotEmpty($model->toArray());

        $this->assertIsArray($model->getAttributes());

    }

    public function testThatFactoryReturnsModelCollection()
    {
        $modelCollection = Item::factory(2)->make();

        $this->assertInstanceOf(ModelCollection::class, $modelCollection);

        $this->assertInstanceOf(Item::class, $modelCollection->first());

    }

    public function testThatFactoryReturnsModelWithComponentsUsingItemCode()
    {
        $component = Item::first();

        $model = Item::factory()->makeWithComponents($component->code);

        $this->assertInstanceOf(Item::class, $model);

        $this->assertSame($component->code, $model['components'][0]['code']);
    }

    public function testThatFactoryReturnsModelWithComponentsUsingItemCodes()
    {
        $components = Item::paginate(1, 2);

        $component1 = $components[0];

        $component2 = $components[1] ?? $components[0];

        $model = Item::factory()->makeWithComponents($component1->code, $component2->code);

        $this->assertInstanceOf(Item::class, $model);

        $this->assertSame($component1->code, $model['components'][0]['code']);

        $this->assertSame($component2->code, $model['components'][1]['code']);
    }

    public function testThatFactoryReturnsModelUsingItemModel()
    {
        $component = Item::first();

        $model = Item::factory()->makeWithComponents($component);

        $this->assertInstanceOf(Item::class, $model);

        $this->assertSame($component->code, $model['components'][0]['code']);
    }

    public function testThatFactoryReturnsModelUsingItemModels()
    {
        $components = Item::paginate(1, 2);

        $component1 = $components[0];

        $component2 = $components[1] ?? $components[0];

        $model = Item::factory()->makeWithComponents(...$components);

        $this->assertInstanceOf(Item::class, $model);

        $this->assertSame($component1->code, $model['components'][0]['code']);

        $this->assertSame($component2->code, $model['components'][1]['code']);
    }

    public function testThatFactoryReturnsModelCollectionUsingItem()
    {
        $component = Item::first();

        $make = 5;

        $modelCollection = Item::factory($make)->makeWithComponents($component);

        $this->assertInstanceOf(ModelCollection::class, $modelCollection);

        $this->assertCount($make, $modelCollection);

        $this->assertInstanceOf(Item::class, $modelCollection->first());

        $this->assertSame($component->code, $modelCollection[0]['components'][0]['code']);

        $this->assertSame($component->code, $modelCollection[$make - 1]['components'][0]['code']);
    }
}