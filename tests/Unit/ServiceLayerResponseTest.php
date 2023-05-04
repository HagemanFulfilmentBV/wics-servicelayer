<?php

namespace Hageman\Wics\ServiceLayer\Tests\Unit;

use Hageman\Wics\ServiceLayer\ServiceLayerResponse;
use Hageman\Wics\ServiceLayer\Tests\TestCase;

class ServiceLayerResponseTest extends TestCase
{
    public function testThatClassHasExpectedAttributes()
    {
        $response = new ServiceLayerResponse(200, 'Expectations met', true, []);
        
        $this->assertClassHasAttribute('code', ServiceLayerResponse::class);
        
        $this->assertClassHasAttribute('message', ServiceLayerResponse::class);
        
        $this->assertClassHasAttribute('success', ServiceLayerResponse::class);
        
        $this->assertClassHasAttribute('data', ServiceLayerResponse::class);
        
        $this->assertIsInt($response->code ?? null);
        
        $this->assertIsString($response->message ?? null);
        
        $this->assertIsBool($response->success ?? null);
        
        $this->assertIsArray($response->data ?? null);
    }
    
    public function testThatClassCanByTreatedAsString()
    {
        $response = new ServiceLayerResponse(200, 'Expectations met', true, []);
        
        $this->assertIsString($response . '');
    }
}