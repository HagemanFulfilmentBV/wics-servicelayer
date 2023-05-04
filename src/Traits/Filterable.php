<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Exception;
use Hageman\Wics\ServiceLayer\HttpQueryBuilder;

trait Filterable
{
    use HasHttpQueryBuilder,
        HasPagination;

    /**
     * Returns a new HttpQueryBuilder instance with the provided filter parameters applied.
     *
     * @param string $field
     * @param string|null $operator
     * @param mixed|null $value
     *
     * @return HttpQueryBuilder
     *
     * @throws Exception
     */
    public static function where(string $field, string|null $operator = null, mixed $value = null): HttpQueryBuilder
    {
        return (static::query())->where($field, $operator, $value);
    }
}