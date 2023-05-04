<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Hageman\Wics\ServiceLayer\Factories\ServiceLayerFactory;

trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     *
     * @param  int|null  $count
     *
     * @return ServiceLayerFactory<static>
     */
    public static function factory(int|null $count = null): ServiceLayerFactory
    {
        return (ServiceLayerFactory::factoryForModel(get_called_class()))
            ->count($count);
    }
}