<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\ServiceLayer;

trait Creatable
{
    use Savable;

    /**
     * Creates a new model collection with given attributes and saves directly to the ServiceLayer.
     * Returns created model collection on success, null otherwise.
     *
     * @param ModelCollection $collection
     *
     * @return ModelCollection|null
     */
    private static function createMany(ModelCollection $collection): ?ModelCollection
    {
        $success = ServiceLayer::post(static::$endpoint . static::$createManyEndpoint, $collection->map(function ($instance) {
            return $instance->getFillableAttributes();
        }))?->success ?? false;

        return $success ? $collection : null;
    }

    /**
     * Creates a new model (collection) with given attributes and saves directly to the ServiceLayer.
     * Returns created model (collection) on success, null otherwise.
     *
     * @param array $attributes
     *
     * @return static|ModelCollection|null
     */
    public static function create(array ...$attributes): static|ModelCollection|null
    {
        $collection = new ModelCollection();

        self::addAttributeSetToCollection($collection, $attributes);
        
        if($collection->count() === 0) return null;

        if($collection->count() > 1 && self::$canCreateMany) return self::createMany($collection);

        $instances = new ModelCollection();

        foreach($collection as $instance) {
            $saved = $instance->save();

            if($saved) $instances->add($instance);
        }

        if(!$instances->count()) return null;

        return $instances->count() === 1 ? $instances->first() : $instances;
    }

    /**
     * Adds attribute set to collection to be created.
     * 
     * @param $collection
     * @param $attributeSet
     *
     * @return void
     */
    protected static function addAttributeSetToCollection(&$collection, $attributeSet): void
    {
        if (isset($attributeSet[0]) && is_array($attributeSet[0])) {
            foreach ($attributeSet as $attributes) {
                self::addAttributeSetToCollection($collection, $attributes);
            }
        } else if (is_array($attributeSet)) {
            $collection->add(new self($attributeSet));
        }
    }
}