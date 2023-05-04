<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Hageman\Wics\ServiceLayer\Models\ServiceLayerModel;
use Hageman\Wics\ServiceLayer\ServiceLayer;

trait Searchable
{
    /**
     * Returns model resolved from the ServiceLayer.
     *
     * @param mixed $identifier
     *
     * @return ServiceLayerModel|null
     */
    public static function search(mixed $identifier): ?ServiceLayerModel
    {
        $response = ServiceLayer::get(static::$endpoint . '/' . $identifier);

        if(!$response->success) return null;

        $model = new self($response->data[0] ?? $response->data);

        $model->newlyCreated = false;

        return $model;
    }
}