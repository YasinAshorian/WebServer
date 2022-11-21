<?php

namespace Yasinashourian\WebServer\webserver;


class Server
{

    protected $host;

    protected $port;

    protected $socket;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = (int) $port;

        $this->createSocket();

        $this->bindSocket();
    }

    public function listen(callable $callback)
    {
        if ( !is_callable( $callback ) ) throw new \Exception('The given argument should be callable.');

        while (true)
        {
            socket_listen($this->socket);

            if (! $client = socket_accept($this->socket)) {
                socket_close($client);
                continue;
            }

            $request = Request::withHeaderString(socket_read($client, 1024));

            $response = call_user_func($callback, $request);

            if (! $response or $response instanceof Response) {
                $response = Response::error(404);
            }

            $response = (string) $response;

            socket_write($client, $response, strlen($response));

            socket_close($client);
        }

    }
    protected function createSocket()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    }

    protected function bindSocket()
    {
        if (!socket_bind($this->socket, $this->host, $this->port)) {
            throw new \Exception( 'Could not bind: '.$this->host.':'.$this->port.' - '.socket_strerror(socket_last_error()));
        }
    }
}