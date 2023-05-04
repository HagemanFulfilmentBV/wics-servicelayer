<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\HttpQueryBuilder;

trait HasPagination
{
    use HasHttpQueryBuilder;

    /**
     * Alias for paginate() -> retrieve all collections.
     * Returns a model collection resolved from the ServiceLayer.
     *
     * @return ModelCollection
     */
    public static function all(): ModelCollection
    {
        return self::paginate(1, -1);
    }

    /**
     * Alias for paginate() -> retrieve array of collections.
     * Returns a model collection resolved from the ServiceLayer.
     *
     * @return ModelCollection
     */
    public static function list(): ModelCollection
    {
        return self::paginate();
    }

    /**
     * Alias for paginate() -> retrieve first collection.
     * Returns a model resolved from the ServiceLayer.
     *
     * @return null|static
     */
    public static function first(): ?static
    {
        return self::paginate(1, 1)?->first();
    }

    /**
     * Returns a model collection resolved from the ServiceLayer.
     *
     * @param int $page
     * @param int $pageSize
     *
     * @return ModelCollection
     */
    public static function paginate(int $page = 1, int $pageSize = 10): ModelCollection
    {
        return (new HttpQueryBuilder(static::class))->paginate($page, $pageSize);
    }
}