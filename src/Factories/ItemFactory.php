<?php

namespace Hageman\Wics\ServiceLayer\Factories;

use Hageman\Wics\ServiceLayer\Models\Item;
use Hageman\Wics\ServiceLayer\Models\ServiceLayerModel;

class ItemFactory extends ServiceLayerFactory
{
    /**
     * The class of the factory's corresponding model.
     *
     * @var class-string<ServiceLayerModel>
     */
    protected static string $model = Item::class;

    /**
     * Make a new model using the factory with the use of user provided items as components.
     *
     * @param ...$item
     *
     * @return mixed
     */
    public function makeWithComponents(...$item): mixed
    {
        return $this->make([
            'assembled' => true,
            'assemblyType' => ['picking', 'stock'][rand(0, 1)],
            'components' => array_map(function($i) use($item) {
                return [
                    'code' => is_a($item[$i], Item::class) ? $item[$i]->code : $item[$i],
                    'skuCode' => 'ST',
                    'quantity' => rand(1,5),
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
        $bestBefore = ['', 'bestbefore', 'production'][rand(0,2)];
        $bestBeforeDays = rand(365, 600);
        
        return array_replace([
            'code' => 'T_' . date('YmdHis') . '-' . explode('.', (string) microtime(true))[1],
            'description' => 'Test item ' . date('YmdHis'),
            'internalDescription' => 'Test item ' . date('YmdHis'),
            'assembled' => false,
            'itemGroup' => 'ALG',
            'defaultUnit' => 'ST',
            'statusCode' => 10,
            'weight' => rand(1, 18),
            'bestBefore' => [
                'receiptDateEntry' => $bestBefore,
                'bestBeforePeriod' => empty($bestBefore) ? 0 : $bestBeforeDays,
                'quarantinePeriod' => empty($bestBefore) ? 0 : rand($bestBeforeDays, 610),
                'warrantyPeriod' => empty($bestBefore) ? 0 : rand(610, 800),
            ],
            'units' => array_map(function ($i) {
                return [
                    'unit' => ['ST', 'DS', 'PAL'][$i],
                    'length' => [rand(20, 40), rand(50, 90), 120][$i],
                    'width' => [rand(10, 25), rand(35, 60), 80][$i],
                    'height' => [rand(5, 20), rand(20, 45), rand(140, 180)][$i],
                    'amountSKU' => [1, rand(2, 6), rand(12, 600)][$i],
                ];
            }, range(0, rand(0, 2))),
            'warehouses' => [
                [
                    'code' => 'HGZ',
                    'minimumQuantity' => 1,
                    'maximumQuantity' => rand(5000, 25000),
                    'safeQuantity' => rand(10, 50),
                    'reorderQuantity' => rand(25, 100)
                ]
            ]
        ], $attributes);
    }
}