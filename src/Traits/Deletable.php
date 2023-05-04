<?php

namespace Hageman\Wics\ServiceLayer\Traits;

use Hageman\Wics\ServiceLayer\Models\ServiceLayerModel;
use Hageman\Wics\ServiceLayer\ServiceLayer;

trait Deletable
{
    private static function merge_identifiers($identifier) {
        $identifiers = [];

        if(is_iterable($identifier)) {
            foreach($identifier as $id) {
                if(is_a($id, ServiceLayerModel::class)) {
                    if($id->getAttribute(static::$identifierField)) $identifiers[] = $id->getAttribute(static::$identifierField);
                } else {
                    $identifiers = array_merge($identifiers, self::merge_identifiers($id));
                }
            }
        } else {
            $identifiers[] = $identifier;
        }

        return $identifiers;
    }

    /**
     * Destroys identifiers from the ServiceLayer.
     *
     * @param mixed $identifier
     * @return bool
     */
    public static function destroy(mixed ...$identifier): bool
    {
        $identifiers = self::merge_identifiers($identifier);

        if(empty($identifiers)) return false;

        foreach($identifiers as $id) {
            $response = ServiceLayer::delete(static::$endpoint . '/' . $id);

            if(!$response->success) return false;
        }

        return true;
    }

    /**
     * Deletes current from the ServiceLayer.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $response = ServiceLayer::delete(static::$endpoint . '/' . $this->getAttribute($this::$identifierField));

        return $response->success;
    }
}