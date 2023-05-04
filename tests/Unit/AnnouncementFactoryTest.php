<?php

namespace Hageman\Wics\ServiceLayer\Tests\Unit;

use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\Models\Announcement;
use Hageman\Wics\ServiceLayer\Models\Item;
use Hageman\Wics\ServiceLayer\Models\ServiceLayerModel;
use Hageman\Wics\ServiceLayer\Tests\TestCase;

class AnnouncementFactoryTest extends TestCase
{
    public function testThatFactoryReturnsModel()
    {
        $model = Announcement::factory()->make();

        $this->assertInstanceOf(ServiceLayerModel::class, $model);

        $this->assertIsObject($model);

        $this->assertNotEmpty($model->toArray());

        $this->assertIsArray($model->getAttributes());

    }

    public function testThatFactoryReturnsModelCollection()
    {
        $modelCollection = Announcement::factory(2)->make();

        $this->assertInstanceOf(ModelCollection::class, $modelCollection);

        $this->assertInstanceOf(ServiceLayerModel::class, $modelCollection->first());

    }

    public function testThatFactoryReturnsModelUsingItemCode()
    {
        $item = Item::first();

        $model = Announcement::factory()->makeUsingItems($item->code);

        $this->assertInstanceOf(ServiceLayerModel::class, $model);

        $this->assertSame($item->code, $model['lines'][0]['itemCode']);
    }

    public function testThatFactoryReturnsModelUsingItemCodes()
    {
        $items = Item::paginate(1, 2);

        $item1 = $items[0];

        $item2 = $items[1] ?? $items[0];

        $model = Announcement::factory()->makeUsingItems($item1->code, $item2->code);

        $this->assertInstanceOf(ServiceLayerModel::class, $model);

        $this->assertSame($item1->code, $model['lines'][0]['itemCode']);

        $this->assertSame($item2->code, $model['lines'][1]['itemCode']);
    }

    public function testThatFactoryReturnsModelUsingItemModel()
    {
        $item = Item::first();

        $model = Announcement::factory()->makeUsingItems($item);

        $this->assertInstanceOf(ServiceLayerModel::class, $model);

        $this->assertSame($item->code, $model['lines'][0]['itemCode']);
    }

    public function testThatFactoryReturnsModelUsingItemModels()
    {
        $items = Item::paginate(1, 2);

        $item1 = $items[0];

        $item2 = $items[1] ?? $items[0];

        $model = Announcement::factory()->makeUsingItems(...$items);

        $this->assertInstanceOf(ServiceLayerModel::class, $model);

        $this->assertSame($item1->code, $model['lines'][0]['itemCode']);

        $this->assertSame($item2->code, $model['lines'][1]['itemCode']);
    }

    public function testThatFactoryReturnsModelCollectionUsingItem()
    {
        $item = Item::first();

        $make = 5;

        $modelCollection = Announcement::factory($make)->makeUsingItems($item->code);

        $this->assertInstanceOf(ModelCollection::class, $modelCollection);

        $this->assertCount($make, $modelCollection);

        $this->assertInstanceOf(ServiceLayerModel::class, $modelCollection->first());

        $this->assertSame($item->code, $modelCollection[0]['lines'][0]['itemCode']);

        $this->assertSame($item->code, $modelCollection[$make - 1]['lines'][0]['itemCode']);
    }
}