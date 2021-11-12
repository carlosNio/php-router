<?php

namespace Nio\Router;

class Request
{
    private $path;
    private $method;
    private $uri;
    private $host;

    private HeadersBag $headers;
    private ParametersBag $params;

    public function __construct()
    {
        $server = $_SERVER;
        $this->path = $server['PATH_INFO'] ?? '/';
        $this->method = strtoupper($server['REQUEST_METHOD']);
        $this->uri = $server['REQUEST_URI'];
        $this->host = $server['HTTP_HOST'];

        $this->params = new ParametersBag(array_merge($_POST, $_GET, $_FILES));
        $this->headers = $this->getHeaders($server);
    }


    /**
     * extract all headers
     * 
     * @return HeadersBag
     */
    private function getHeaders(array $server): HeadersBag
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = strtolower(substr($key, 5));
                $name = str_replace("_" , "-" , $name);
                $headers[$name] = $value;
            }
        }

        $bag = new HeadersBag($headers);
        return $bag;
    }


    public function path()
    {
        return $this->path;
    }

    public function method()
    {
        return $this->method;
    }

    public function isMethod(string $method)
    {
        $method = strtoupper($method);
        return $this->method === $method;
    }

    public function uri()
    {
        return $this->uri;
    }

    public function host()
    {
        return $this->host;
    }


    /**
     * return request data
     * 
     * @return ParametersBag
     */
    public function params(): ParametersBag
    {
        return $this->params;
    }


    /**
     * return all headers
     * 
     * @return HeadersBag
     */

    public function headers() : HeadersBag
    {
        return $this->headers;
    }


    /**
     * return a boolean if the request is with XMLHttp
     * 
     * @return bool
     */

    public function isAjax()
    {
        return 'XMLHttpRequest' === $this->headers->get('x-requested-with');
    }


    /**
     * return a raw data , in json format by default
     * 
     * @return mixed
     */
    public function raw_data($format = 'json')
    {
        $rawdata = file_get_contents("php://input");
        if ($format == 'json') return json_decode($rawdata);
        if ($format == 'xml') return simplexml_load_string($rawdata);
    }
}
