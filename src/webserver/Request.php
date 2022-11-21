<?php

namespace Yasinashourian\WebServer\webserver;

class Request
{

    protected $method;

    protected $uri;

    protected $parameters;

    protected $headers;

    /**
     * Request constructor
     *
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @return void
     */
    public function __construct($method, $uri, $headers = [])
    {
        $this->headers = $headers;
        $this->method = strtoupper($method);

        // split uri and parameters string
        list($this->uri, $params) = explode('?', $uri);

        // parse the parameters
        parse_str($params, $this->parameters);
    }

    /**
     * Create new request instance using a string header
     *
     * @param string $header
     * @return Request
     */
    public static function withHeaderString($header)
    {
        $lines = explode("\n", $header);

        // method and uri
        list($method, $uri) = explode(' ', array_shift($lines));

        $headers = [];

        foreach ($lines as $line) {
            // clean the line
            $line = trim($line);

            if (strpos($line, ': ') !== false) {
                list($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }

        // create new request object
        return new static($method, $uri, $headers);
    }

}