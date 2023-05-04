<?php

namespace Hageman\Wics\ServiceLayer\Tests\Feature;

use Hageman\Wics\ServiceLayer\Requests\Login;
use Hageman\Wics\ServiceLayer\Tests\TestCase;

class LoginTest extends TestCase
{
    public function testCanAuthenticate()
    {
        $this->assertTrue((new Login)());
    }
}