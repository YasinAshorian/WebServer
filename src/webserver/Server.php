<?php

namespace Yasinashourian\WebServer\webserver;


class Server
{
    /**
     * The current host ! for Example : 127.0.0.1
     *
     * @var string
     */
    protected $host;

    /**
     * The current port ! for Example 8000
     *
     * @var int
     */
    protected $port;

    /**
     * The binded socket
     *
     * @var resource
     */
    protected $socket;

    /**
     *  Construct new Server instance
     *
     * @param string $host
     * @param int $port
     * @throws \Exception
     */
    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = (int) $port;

        /* ---------------------------------
        | create a socket
        |-----------------------------------
        */
        $this->createSocket();
        /*--------------------------------------
        | bind the socket
        |---------------------------------------
        */
        $this->bindSocket();
    }

    /**
     * Listen for requests
     *
     * @param callable $callback
     * @return void
     * @throws \Exception
     */
    public function listen(callable $callback)
    {
        /* ------------------------------------------
        |  check if the callback is valid
        |--------------------------------------------
        */
        if ( !is_callable( $callback ) ) throw new \Exception('The given argument should be callable.');

        while (true)
        {
            /* ------------------------------------------
            |  Listen for connection
            |--------------------------------------------
            */
            socket_listen($this->socket);

            /* ---------------------------------------------------------
            | try to get the client socket resource.
            |
            | if false we got an error close the connection and continue
            |------------------------------------------------------------
            */
            if (! $client = socket_accept($this->socket)) {
                socket_close($client);
                continue;
            }

            /* ----------------------------------------------------------------------
            | create new request instance with the clients' header.
            |
            | In the real world of course you cannot just fix the max size to 1024..
            |-------------------------------------------------------------------------
            */
            $request = Request::withHeaderString(socket_read($client, 1024));

            /* ------------------------------------------
            |  Execute the callback
            |--------------------------------------------
            */
            $response = call_user_func($callback, $request);

            /* ----------------------------------------------
            | Check if we really received a Response object
            |
            | If not return a 404 response object
            |------------------------------------------------
            */
            if (!$response or ! $response instanceof Response) {
                $response = Response::error(404);
            }

            /* ------------------------------------------
            | make a string out of our response
            |--------------------------------------------
            */
            $response = (string) $response;

            /* ------------------------------------------
            | write the response to the client socket
            |--------------------------------------------
            */
            socket_write($client, $response, strlen($response));

            /* -----------------------------------------------
            | close the connection, so we can accept new ones
            |-------------------------------------------------
            */
            socket_close($client);
        }

    }

    /**
     * Create new socket resource
     *
     * @return void
     */
    protected function createSocket()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    }

    /**
     * Bind the socket resource
     *
     * @return void
     * @throws \Exception
     */
    protected function bindSocket()
    {
        if (!socket_bind($this->socket, $this->host, $this->port)) {
            throw new \Exception( 'Could not bind: '.$this->host.':'.$this->port.' - '.socket_strerror(socket_last_error()));
        }
    }
}