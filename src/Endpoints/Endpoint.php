<?php
namespace WoowUpV2\Endpoints;

use WoowUpV2\Models;

/**
 *
 */
class Endpoint
{
    const HTTP_OK                  = 200;
    const HTTP_CREATED             = 201;
    const HTTP_BAD_REQUEST         = 403;
    const HTTP_NOT_FOUND           = 404;
    const HTTP_GONE                = 410;
    const HTTP_TOO_MANY_REQUEST    = 429;
    const HTTP_BAD_GATEWAY         = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;

    const MAX_ATTEMPTS = 25;

    protected static $retryResponses = [
        self::HTTP_TOO_MANY_REQUEST,
        self::HTTP_BAD_GATEWAY,
        self::HTTP_SERVICE_UNAVAILABLE,
    ];

    protected $host;
    protected $apikey;
    protected $http;

    public function __construct($host, $apikey, \GuzzleHttp\ClientInterface $http = null)
    {
        $this->host   = $host;
        $this->apikey = $apikey;
        $this->http   = $http ?: new \GuzzleHttp\Client([
            'timeout'         => 30,
            'connect_timeout' => 10,
        ]);
    }

    protected function get($url, $params = [])
    {
        return $this->request('GET', $url, [
            'query'   => $params,
            'headers' => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function getAsync($url, $params = [])
    {
        return $this->requestAsync('GET', $url, [
            'query'   => $params,
            'headers' => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function post($url, $data, $params = [])
    {
        return $this->request('POST', $url, [
            'query'   => $params,
            'json'    => $data,
            'headers' => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function postAsync($url, $data)
    {
        return $this->requestAsync('POST', $url, [
            'json'    => $data,
            'headers' => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function postForm($url, $params)
    {
        return $this->request('POST', $url, [
            'form_params' => $params,
            'headers'     => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function postFile($url, $file, $params)
    {
        $multipart = [
            [
                'name'     => 'file',
                'contents' => is_resource($file) ? $file : fopen($file, 'r'),
            ],
        ];
        foreach ($params as $key => $value) {
            $multipart[] = [
                'name'     => $key,
                'contents' => $value,
            ];
        }

        return $this->request('POST', $url, [
            'multipart' => $multipart,
            'headers'   => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function put($url, $data, $params = [])
    {
        return $this->request('PUT', $url, [
            'query'   => $params,
            'json'    => $data,
            'headers' => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function putAsync($url, $data)
    {
        return $this->requestAsync('PUT', $url, [
            'json'    => $data,
            'headers' => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function delete($url)
    {
        return $this->request('DELETE', $url, [
            'headers' => [
                'Authorization' => 'Basic ' . $this->apikey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    protected function requestAsync($verb, $url, $params)
    {
        $attempts = 0;

        $retry = function ($e) use ($verb, $url, $params, &$attempts, &$retry) {
            $this->assertRetryable($e, $attempts);

            sleep($this->calculateSleep($e->getResponse(), $attempts));
            $attempts++;

            return $this->http->requestAsync($verb, $url, $params)->otherwise($retry);
        };

        return $this->http->requestAsync($verb, $url, $params)->otherwise($retry);
    }

    protected function request($verb, $url, $params)
    {
        $attempts = 0;

        while ($attempts < self::MAX_ATTEMPTS) {
            try {
                return $this->http->request($verb, $url, $params);
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $this->assertRetryable($e, $attempts);

                sleep($this->calculateSleep($e->getResponse(), $attempts));
                $attempts++;
            }
        }

        throw new \Exception("Max request attempts reached");
    }

    /**
     * @param \Exception $e
     * @param int        $attempts
     * @throws \Exception
     */
    private function assertRetryable($e, $attempts)
    {
        if (!$e instanceof \GuzzleHttp\Exception\RequestException || !$e->hasResponse()) {
            throw $e;
        }

        $statusCode = $e->getResponse()->getStatusCode();

        if (!in_array($statusCode, self::$retryResponses) || $attempts >= self::MAX_ATTEMPTS) {
            throw $e;
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int                                  $attempts
     * @return int
     */
    private function calculateSleep($response, $attempts)
    {
        if ($response->getStatusCode() === self::HTTP_TOO_MANY_REQUEST) {
            $retryAfter = (int) $response->getHeaderLine('Retry-After');
            if ($retryAfter > 0) {
                return $retryAfter;
            }
        }

        return (int) pow(2, $attempts);
    }

    protected function encode($string)
    {
        return urlencode(base64_encode($string));
    }
}
