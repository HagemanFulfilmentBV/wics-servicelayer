<?php

namespace Hageman\Wics\ServiceLayer\Models;

use Hageman\Wics\ServiceLayer\ServiceLayer;
use Hageman\Wics\ServiceLayer\Traits\Creatable;
use Hageman\Wics\ServiceLayer\Traits\Filterable;
use Hageman\Wics\ServiceLayer\Traits\HasFactory;
use Hageman\Wics\ServiceLayer\Traits\Savable;

class Item extends ServiceLayerModel
{    
    use HasFactory,
        Creatable,
        Savable,
        Filterable;

    /**
     * The identifier field of the model.
     *
     * @var string
     */
    protected static string $identifierField = 'code';

    /**
     * Indicates whether this model can create more than one in a single request.
     *
     * @var bool
     */
    protected static bool $canCreateMany = true;

    /**
     * Find related item code by searching the given EAN.
     *
     * @param string $ean
     * @return array|mixed
     */
    public static function ean_to_item_code(string $ean): mixed
    {
        $response = ServiceLayer::get(static::$endpoint . '/ean-item/' . $ean);

        return $response->success ? (is_array($response->data) ? $response->data[0] : $response->data) : null;
    }

    /**
     * Find related EAN by searching the given item code.
     *
     * @param string $itemCode
     * @return array|mixed
     */
    public static function item_code_to_ean(string $itemCode): mixed
    {
        $response = ServiceLayer::get(static::$endpoint . '/item-ean/' . $itemCode);

        return $response->success ? (is_array($response->data) ? $response->data[0] : $response->data) : null;
    }
}