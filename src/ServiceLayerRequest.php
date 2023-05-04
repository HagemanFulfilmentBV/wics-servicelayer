<?php

namespace Hageman\Wics\ServiceLayer;

use Hageman\Wics\ServiceLayer\Auth\Basic;
use JsonSerializable;

final class ServiceLayerRequest implements JsonSerializable
{
    /**
     * @var string
     */
    private string $host;

    /**
     * @var int
     */
    private int $port;

    /**
     * @var string
     */
    private string $url;

    /**
     * Initiates a new request.
     *
     * @param string            $method
     * @param string            $uri
     * @param array|string|null $data
     */
    public function __construct(
        private string            $method,
        private string            $uri,
        private array|string|null $data = null,
    )
    {
        if (is_array($this->data)) $this->data = json_encode($this->data);

        $this->host = (string)config('wics.service-layer.host');

        $this->port = (int)config('wics.service-layer.port', 80);

        $this->method = strtoupper($this->method);

        $this->url = str_replace(' ', '%20', $this->host . (in_array($this->port, [80, 443]) ? '' : ":$this->port") . '/' . trim($this->uri, '/'));

        $this->request();
    }

    /**
     * Perform a curl request on the ServiceLayer.
     *
     * @return void
     */
    private function request(): void
    {
        ServiceLayer::$request = $this;
        
        $curlOptions = [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . Basic::hash(config('wics.service-layer.key'), config('wics.service-layer.secret')),
            ],
        ];

        if (in_array($this->method, ['POST', 'PUT'])) {
            $curlOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            $curlOptions[CURLOPT_POSTFIELDS] = $this->data;
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);

        $response = curl_error($curl) ? json_encode([
            'code' => 500,
            'message' => curl_error($curl),
            'success' => false,
            'data' => $response,
        ]) : json_decode($response, true);

        curl_close($curl);

        ServiceLayer::$response = new ServiceLayerResponse(...[
            'code' => $response['code'] ?? 204,
            'message' => $response['message'] ?? 'No content',
            'success' => $response['success'] ?? false,
            'data' => $response['data'] ?? null,
        ]);
    }

    /**
     * Return the response of handled request when class is called as a function.
     *
     * @return ServiceLayerResponse
     */
    public function __invoke(): ServiceLayerResponse
    {
        return $this::get_latest_response();
    }

    /**
     * Return the latest response of the ServiceLayer or create a new 'No Content' response.
     *
     * @return ServiceLayerResponse
     */
    private static function get_latest_response(): ServiceLayerResponse
    {
        return ServiceLayer::$response ?? new ServiceLayerResponse(...[
                'code' => $response['code'] ?? 204,
                'message' => $response['message'] ?? 'No content',
                'success' => $response['success'] ?? false,
                'data' => $response['data'] ?? null,
            ]);
    }

    /**
     * Return the request as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "$this->url: ($this->method)" . ($this->count() ? " [data:{$this->count()}]" : '');
    }

    /**
     * Return to data count of the request.
     *
     * @return int
     */
    public function count(): int
    {
        if(is_null($this->data)) return 0;

        $data = is_string($this->data) ? json_decode($this->data, true) : $this->data;

        return is_countable($data) ? count($data) : 0;
    }

    /**
     * Return an array with some attributes of the request.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data,
            'method' => $this->method,
            'url' => $this->url,
        ];
    }

    /**
     * Return the data that was sent in request.
     *
     * @return array|string|null
     */
    public function getData(): array|string|null
    {
        return $this->data;
    }
}