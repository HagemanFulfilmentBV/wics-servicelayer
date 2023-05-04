<?php

namespace Hageman\Wics\ServiceLayer\Auth;

class Basic
{
    /**
     * @param string $key
     * @param string $secret
     *
     * @return string
     */
    public static function hash(string $key, string $secret): string
    {
        return 'Basic ' . base64_encode("$key:$secret");
    }
}