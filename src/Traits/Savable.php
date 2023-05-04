<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Hageman\Wics\ServiceLayer\ServiceLayer;

trait Savable
{
    /**
     * Posts current model with all attributes to the ServiceLayer.
     *
     * @return bool
     */
    private function saveNewModel(): bool
    {
        return ServiceLayer::post(static::$endpoint, $this->getFillableAttributes())?->success ?? false;
    }

    /**
     * Puts current model with 'dirty' attributes only to the ServiceLayer using its identifier field.
     *
     * @return bool
     */
    private function saveExistingModel(): bool
    {
        return ServiceLayer::put(static::$endpoint . '/' . $this->getAttribute($this::$identifierField), $this->getDirtyAttributes())?->success;
    }

    /**
     * Saves the identifier into the model as an attribute.
     *
     * @return void
     */
    private function saveIdentifierAsAttribute(): void
    {
        if(!empty(ServiceLayer::$response->data)) $this->setAttribute($this::$identifierField, ServiceLayer::$response->data[0] ?? ServiceLayer::$response->data);
    }

    /**
     * Saves current model to the ServiceLayer.
     *
     * @return bool
     */
    public function save(): bool
    {
        $success = $this->newlyCreated ? $this->saveNewModel() : $this->saveExistingModel();

        if($success) {
            $this->saveIdentifierAsAttribute();

            $this->clearDirtyAttributes();
        }

        return $success;
    }
}