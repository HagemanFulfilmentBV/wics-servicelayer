<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

trait HasAttributes
{    
    /**
     * Array of fillable attributes.
     *
     * @var array
     */
    protected array $fillable = [];

    /**
     * Array of guarded attributes.
     *
     * @var array
     */
    protected array $guarded = [];

    /**
     * Array of hidden attributes.
     *
     * @var array
     */
    protected array $hidden = [];

    /**
     * Array of visible attributes.
     *
     * @var array
     */
    protected array $visible = [];

    /**
     * Array of attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Array of dirty attributes.
     *
     * @var array
     */
    protected array $dirty = [];

    /**
     * Attribute scheme.
     *
     * @var array|null
     */
    protected array|null $scheme = null;

    /**
     * Casts attribute to specific type.
     * 
     * @param mixed  $value
     * @param string $attribute
     *
     * @return mixed
     */
    protected function castAttribute(mixed $value, string $attribute): mixed
    {
        if (is_null($this->scheme)) return $value;

        $type = Arr::dot($this->scheme['fields'])[$this->attributeNamespace($attribute)] ?? 'string';

        if (!$this->attributeOfType($attribute, $type)) settype($value, $type);

        return $value;
    }

    /**
     * Checks if attribute is of same type as provided.
     * 
     * @param string $attribute
     * @param string $type
     *
     * @return bool
     */
    public function attributeOfType(string $attribute, string $type): bool
    {
        return gettype(data_get($this->attributes, $attribute)) === $type;
    }

    /**
     * Checks if attribute exists in scheme.
     * 
     * @param string $attribute
     *
     * @return bool
     */
    protected function schemeHasAttribute(string $attribute): bool
    {
        if (is_null($this->scheme)) return true;

        return isset(Arr::dot($this->scheme['fields'])[$this->attributeNamespace($attribute)]);
    }

    /**
     * Serializes instance into an array.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->getAttributes();
    }

    /**
     * Get an (visible/non-hidden) attribute.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function getAttribute(string $attribute): mixed
    {
        if (!$this->schemeHasAttribute($attribute)) return null;

        return $this->castAttribute(data_get($this->attributes, $attribute), $attribute);
    }

    /**
     * Get all attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get all fillable/non-guarded attributes existing in scheme.
     *
     * @return array
     */
    public function getFillableAttributes(): array
    {
        $attributes = [];
        
        foreach(array_keys(Arr::dot($this->attributes)) as $attribute) {
            $attributeNamespace = $this->attributeNamespace($attribute);
            
            if(!$this->isGuarded($attributeNamespace) && $this->schemeHasAttribute($attributeNamespace)) $attributes[$attribute] = $this->getAttribute($attribute);
        }
        
        return Arr::undot($attributes);
    }

    /**
     * Get all visible/non-hidden attributes.
     *
     * @return array
     */
    public function getVisibleAttributes(): array
    {
        $attributes = [];
        
        foreach(array_keys(Arr::dot($this->attributes)) as $attribute) {
            if(!$this->isHidden($this->attributeNamespace($attribute))) $attributes[$attribute] = $this->getAttribute($attribute);
        }
        
        return Arr::undot($attributes);
    }

    /**
     * Get dirty attributes.
     *
     * @return array
     */
    public function getDirtyAttributes(): array
    {
        $attributes = [];

        foreach(array_keys(Arr::dot($this->attributes)) as $attribute) {
            $attributeNamespace = $this->attributeNamespace($attribute);

            if($this->isDirty($attribute) && $this->schemeHasAttribute($attributeNamespace)) $attributes[$attribute] = $this->getAttribute($attribute);
        }

        return Arr::undot($attributes);
    }

    /**
     * Set an attribute.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return void
     */
    public function setAttribute(string $attribute, mixed $value): void
    {
        data_set($this->attributes, $attribute, $this->castAttribute($value, $attribute));
    }

    /**
     * Set multiple attributes.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        foreach (Arr::dot($attributes) as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }

    /**
     * Loads scheme YML file if exists.
     *
     * @return void
     */
    protected function loadScheme(): void
    {
        $model = $this->getModelName() ?? '';

        $ymlFile = __DIR__ . "/../Schemes/$model.yml";

        if (!file_exists($ymlFile)) return;

        $this->setupFromArray(Yaml::parse(file_get_contents($ymlFile)));
    }

    /**
     * Sets scheme based on array.
     * 
     * @param array  $scheme
     * @param string $prefix
     * @param bool   $isHidden
     * @param bool   $isGuarded
     *
     * @return void
     */
    protected function setupFromArray(array $scheme, string $prefix = '', bool $isHidden = false, bool $isGuarded = false): void
    {
        foreach ($scheme as $field => $properties) {
            $field = trim("$prefix.$field", '.');

            if (isset($properties['hidden']) || ($isHidden && !isset($properties['visible']))) {
                $this->hidden[] = $field;
                
                $isHidden = true;
            }

            if (isset($properties['guarded']) || ($isGuarded && !isset($properties['fillable']))) {
                $this->guarded[] = $field;

                $isGuarded = true;
            }

            $type = $properties['type'] ?? 'string';

            $this->scheme['fields'][$field] = $type;

            if ($type === 'array') {
                if (isset($properties['items'])) {
                    $this->setupFromArray($properties['items'], $field, $isHidden, $isGuarded);
                }
            }
        }
    }

    /**
     * Checks if attribute should be hidden. Not accessible via _get.
     * 
     * @param string $attribute
     *
     * @return bool
     */
    protected function isHidden(string $attribute): bool
    {
        $attribute = $this->attributeNamespace($attribute);
        
        return in_array($attribute, $this->hidden) || (!empty($this->visible) && !in_array($attribute, $this->visible));
    }

    /**
     * Checks if attribute should be guarded. Not settable via _set.
     * 
     * @param string $attribute
     *
     * @return bool
     */
    protected function isGuarded(string $attribute): bool
    {
        $attribute = $this->attributeNamespace($attribute);
        
        return in_array($attribute, $this->guarded) || (!empty($this->fillable) && !in_array($attribute, $this->fillable));
    }

    /**
     * Checks if attribute is dirty.
     *
     * @param string $attribute
     *
     * @return bool
     */
    protected function isDirty(string $attribute): bool
    {
        return $this->dirty[$attribute] ?? false;
    }

    /**
     * Clear dirty attributes.
     *
     * @return array
     */
    public function clearDirtyAttributes(): array
    {
        return $this->dirty = [];
    }

    /**
     * @param $attribute
     *
     * @return string
     */
    protected function attributeNamespace($attribute): string
    {
        return preg_replace('/\.\d\./', '.', $attribute);
    }
}