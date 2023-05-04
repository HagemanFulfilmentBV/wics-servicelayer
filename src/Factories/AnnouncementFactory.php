<?php

namespace Hageman\Wics\ServiceLayer\Factories;

use Hageman\Wics\ServiceLayer\Models\Announcement;
use Hageman\Wics\ServiceLayer\Models\Item;
use Hageman\Wics\ServiceLayer\Models\ServiceLayerModel;

class AnnouncementFactory extends ServiceLayerFactory
{
    /**
     * The class of the factory's corresponding model.
     *
     * @var class-string<ServiceLayerModel>
     */
    protected static string $model = Announcement::class;

    /**
     * Make a new model using the factory with the use of user provided items as lines.
     *
     * @param ...$item
     *
     * @return mixed
     */
    public function makeUsingItems(...$item): mixed
    {
        return $this->make([
            'lines' => array_map( function($i) use($item) {
                $wicsItem = Item::where('code', is_a($item[$i], Item::class) ? $item[$i]->code : $item[$i])->first();

                return [
                    'lineNumber' => $i + 1,
                    'itemCode' => $wicsItem->code ?? $item[$i],
                    'itemDescription' => $wicsItem->description ?? '',
                    'unit' => 'ST',
                    'quantityExpected' => rand(1, 200)
                ];
            }, range(0, func_num_args() - 1))
        ]);
    }

    /**
     * Define the model.
     *
     * @param ...$attributes
     *
     * @return array
     */
    public function definition(...$attributes): array
    {
        return array_replace([
            'reference' => 'PO_' . date('YmdHis') . '-' . explode('.', (string) microtime(true))[1],
            'additionalReference' => 'Purchase order',
            'plannedDate' => date('Y-m-d', strtotime('+1 day')),
            'warehouseCode' => 'HGZ',
            'stockStatus' => 31,
            'lines' => [],
        ], $attributes);
    }
}