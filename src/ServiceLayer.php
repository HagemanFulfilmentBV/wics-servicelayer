<?php

namespace Hageman\Wics\ServiceLayer;

class ServiceLayer
{
    /**
     * The default namespace of the package.
     *
     * @var string
     */
    public static string $namespace = 'Hageman\\Wics\\ServiceLayer\\';

    /**
     * The latest request from the ServiceLayer.
     *
     * @var ServiceLayerRequest|null
     */
    public static ServiceLayerRequest|null $request;

    /**
     * The latest response from the ServiceLayer.
     *
     * @var ServiceLayerResponse|null
     */
    public static ServiceLayerResponse|null $response;

    /**
     * Create a 'delete' request on the ServiceLayer.
     *
     * @param string $endpoint
     *
     * @return ServiceLayerResponse
     */
    public static function delete(string $endpoint): ServiceLayerResponse
    {
        return (new ServiceLayerRequest('DELETE', $endpoint))();
    }

    /**
     * Create a 'get' request on the ServiceLayer.
     *
     * @param string $endpoint
     *
     * @return ServiceLayerResponse
     */
    public static function get(string $endpoint): ServiceLayerResponse
    {
        return (new ServiceLayerRequest('GET', $endpoint))();
    }

    /**
     * Create a 'post' request on the ServiceLayer.
     *
     * @param string       $endpoint
     * @param array|string $data
     *
     * @return ServiceLayerResponse
     */
    public static function post(string $endpoint, array|string $data): ServiceLayerResponse
    {
        return (new ServiceLayerRequest('POST', $endpoint, $data))();
    }

    /**
     * Create a 'put' request on the ServiceLayer.
     *
     * @param string       $endpoint
     * @param array|string $data
     *
     * @return ServiceLayerResponse
     */
    public static function put(string $endpoint, array|string $data): ServiceLayerResponse
    {
        return (new ServiceLayerRequest('PUT', $endpoint, $data))();
    }
}