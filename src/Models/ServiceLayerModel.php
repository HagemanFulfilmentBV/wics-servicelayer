<?php

namespace Hageman\Wics\ServiceLayer\Models;

use ArrayAccess;
use Exception;
use Hageman\Wics\ServiceLayer\Traits\HasAttributes;
use Illuminate\Contracts\Support\Arrayable;

abstract class ServiceLayerModel implements Arrayable, ArrayAccess
{
    use HasAttributes;

    /**
     * The endpoint on the ServiceLayer which this ServiceLayerModel talks to.
     *
     * @var string|null
     */
    protected static string|null $endpoint;

    /**
     * The identifier field of the model.
     *
     * @var string
     */
    protected static string $identifierField = 'id';

    /**
     * Indicates whether this model can create more than one in a single request.
     *
     * @var bool
     */
    protected static bool $canCreateMany = false;

    /**
     * Append a string to the endpoint for 'createMany' actions.
     * Default: /list
     * Overridable per class
     *
     * @var string
     */
    protected static string $createManyEndpoint = '/list';

    /**
     * Indicates if the model is newly created or not.
     *
     * @var bool
     */
    public bool $newlyCreated = true;

    /**
     * Create a new ServiceLayerModel instance
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->loadScheme();
        
        $this->setAttributes($attributes);

        $this->setEndpoint();
    }

    /**
     * Magic method to get attribute.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function __get(string $attribute): mixed
    {
        return $this->getAttribute($attribute);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function __isset(string $attribute)
    {
        return $this->offsetExists($attribute);
    }

    /**
     * Magic method to set attribute.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return void
     */
    public function __set(string $attribute, mixed $value)
    {
        $this->setAttribute($attribute, $value);

        $this->dirty[$attribute] = true;
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $attribute
     *
     * @return void
     */
    public function __unset(string $attribute)
    {
        $this->offsetUnset($attribute);
    }

    /**
     * Get the instance as an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        try {
            return !is_null($this->getAttribute($offset));
        } catch(Exception) {
            return false;
        }
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        try {
            return $this->getAttribute($offset);
        } catch(Exception) {
            return null;
        }
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Get the endpoint.
     *
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this::$endpoint;
    }

    /**
     * Gets the model name based on last segment of class string.
     *
     * @return string|null
     */
    private function getModelName(): ?string
    {
        return strtolower(last(explode("\\", static::class)));
    }

    /**
     * Sets the endpoint. Defaults to the model name.
     *
     * @param string|null $endpoint
     * @return void
     */
    private function setEndpoint(string $endpoint = null): void
    {
        $this::$endpoint = $endpoint ?? $this->getModelName();
    }
}