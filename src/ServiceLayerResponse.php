<?php

namespace Hageman\Wics\ServiceLayer;

use JsonSerializable;

final class ServiceLayerResponse implements JsonSerializable
{
    /**
     * Create a new response.
     *
     * @param int    $code
     * @param string $message
     * @param bool   $success
     * @param mixed  $data
     */
    public function __construct(
        public int    $code,
        public string $message,
        public bool   $success,
        public mixed  $data,
    )
    {
        if (!is_array($this->data)) $this->data = (array)$this->data;
    }

    /**
     * Return the response as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $status = $this->success ? 'success' : 'fail';
        return "$status: ($this->code) $this->message" . ($this->count() ? " [data:{$this->count()}]" : '');
    }

    /**
     * Return to data count of the response.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Return an array with some attributes of the response.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'success' => $this->success,
            'data' => $this->data,
        ];
    }
}