<?php

namespace Hageman\Wics\ServiceLayer\Models;

use Hageman\Wics\ServiceLayer\Traits\Creatable;
use Hageman\Wics\ServiceLayer\Traits\Deletable;
use Hageman\Wics\ServiceLayer\Traits\Searchable;
use Hageman\Wics\ServiceLayer\Traits\Filterable;
use Hageman\Wics\ServiceLayer\Traits\HasFactory;
use Hageman\Wics\ServiceLayer\Traits\HasPagination;

class Announcement extends ServiceLayerModel
{    
    use Creatable,
        Deletable,
        Searchable,
        HasFactory,
        HasPagination,
        Filterable;

    /**
     * The endpoint on the ServiceLayer which this ServiceLayerModel talks to.
     *
     * @var string|null
     */
    protected static string|null $endpoint = 'announcement';

    /**
     * The identifier field of the model.
     *
     * @var string
     */
    protected static string $identifierField = 'number';
}