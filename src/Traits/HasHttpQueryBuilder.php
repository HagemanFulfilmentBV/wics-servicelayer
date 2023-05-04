<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Hageman\Wics\ServiceLayer\HttpQueryBuilder;

trait HasHttpQueryBuilder
{

    /**
     * @var array $queryParameters
     */
    protected array $queryParameters = [];


    /**
     * Returns a new HTTP Query Builder.
     *
     * @return HttpQueryBuilder
     */
    public static function query(): HttpQueryBuilder
    {
        return (new HttpQueryBuilder(static::class));
    }
}