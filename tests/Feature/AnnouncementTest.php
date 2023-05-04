<?php

namespace Hageman\Wics\ServiceLayer\Tests\Feature;

use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\Models\Announcement;
use Hageman\Wics\ServiceLayer\Models\Item;
use Hageman\Wics\ServiceLayer\ServiceLayer;
use Hageman\Wics\ServiceLayer\Tests\TestCase;

class AnnouncementTest extends TestCase
{
    public function testCanSaveNewModelWithExistingItemsToServiceLayer()
    {
        $item = Item::first();

        $announcement = Announcement::factory()->makeUsingItems($item->code);

        $saved = $announcement->save();

        $this->assertSame(201, ServiceLayer::$response->code);

        $this->assertStringContainsString('(POST)', ServiceLayer::$request);

        $this->assertTrue($saved);

        $announcement->delete();
    }

    public function testCanDeleteFromServiceLayerThroughModel()
    {
        $item = Item::first();

        $announcement = Announcement::factory()->makeUsingItems($item->code);

        $saved = $announcement->save();

        if(!$saved) {
            $this->assertTrue($saved);

            return;
        }

        $deleted = $announcement->delete();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertStringContainsString('(DELETE)', ServiceLayer::$request);

        $this->assertTrue($deleted);
    }

    public function testCanDestroyFromServiceLayerUsingIdentifiers()
    {
        $item = Item::first();

        $announcements = Announcement::create(Announcement::factory(2)->makeUsingItems($item->code)->toArray());

        if(!$announcements) {
            $this->assertNotNull($announcements);

            return;
        }

        $destroyed = Announcement::destroy($announcements->pluck('number')->toArray());

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertStringContainsString('(DELETE)', ServiceLayer::$request);

        $this->assertTrue($destroyed, ServiceLayer::$request . ' | ' . ServiceLayer::$response);
    }

    public function testCannotSaveNewModelWithUnknownItemsToServiceLayer()
    {
        $announcement = Announcement::factory()->make();

        $saved = $announcement->save();

        $this->assertSame(400, ServiceLayer::$response->code);

        $this->assertStringContainsString('(POST)', ServiceLayer::$request);

        $this->assertFalse($saved);
    }

    public function testCanRetrieveFromServiceLayerUsingPagination()
    {

        $announcements = Announcement::paginate(1, 1);

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $announcements);

        $this->assertCount(1, $announcements->toArray());

        $this->assertInstanceOf(Announcement::class, $announcements->first());
    }

    public function testCanRetrieveFromServiceLayer()
    {
        $announcements = Announcement::list();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $announcements);

        $this->assertInstanceOf(Announcement::class, $announcements->first());
    }

    public function testCanRetrieveAllFromServiceLayer()
    {
        $announcements = Announcement::all();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $announcements);

        $this->assertInstanceOf(Announcement::class, $announcements->first());
    }

    public function testCanRetrieveFirstFromServiceLayer()
    {
        $announcement = Announcement::first();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(Announcement::class, $announcement);
    }

    public function testCanRetrieveFromServiceLayerUsingFilter()
    {
        $announcements = Announcement::where('stockstatus', 31)->list();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $announcements);

        $this->assertSame(31, $announcements->first()?->stockStatus ?? null);
    }

    public function testCanRetrieveFromServiceLayerUsingFilterWithOperator()
    {
        $announcements = Announcement::where('stockstatus', '=', 31)->list();

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(ModelCollection::class, $announcements);

        $this->assertSame(31, $announcements->first()?->stockStatus ?? null);
    }

    public function testCanRetrieveFromServiceLayerUsingIdentifier()
    {
        $item = Item::first();

        $announcement = Announcement::factory()->makeUsingItems($item->code);

        $saved = $announcement->save();

        if(!$saved) {
            $this->assertTrue($saved);

            return;
        }

        $identifier = $announcement->number;

        $announcement = Announcement::find($identifier);

        $this->assertSame(200, ServiceLayer::$response->code);

        $this->assertInstanceOf(Announcement::class, $announcement);

        $this->assertSame($identifier, $announcement->number ?? null);

        $announcement->delete();
    }
}