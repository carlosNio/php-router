<?php

namespace Nio\Router;

use Closure;
use Exception;
use ReflectionFunction;
use ReflectionMethod;

class Router
{
    private $notFoundAction;

    /**
     * Pattern used in params
     * 
     */
    private $patterns = [
        'number' => '\d+',
        'digit' => '\d',
        "integer" => "\-?[0-9]+",
        "char" => "\w",
        'string' => '\w+',
        'alfa' => '[a-zA-Z]+',
        'text' => '\w+\+?',
        'alfanum' => '[a-zA-Z0-9]+',
        "url_title" => "[\w\-\_]+",
        "date" => "\d{4}-\d{2}-\d{2}",
        'uuid' => '[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9\-]{12}'
    ];


    /**
     *  return a value by her type
     * 
     **/
    private function typedValue($type, &$value)
    {
        switch ($type) {
            case "int":
                $value = intval($value);
                break;
            case "bool":
                $value = boolval($value);
                break;
            case "string":
                $value = strval($value);
                break;
            case "float":
                $value = floatval($value);
        }

        return $value;
    }


    /**
     * get the information about route closure or controller method arguments and give that
     * 
     * @author Carlos Bumba
     */
    private function argsParameters(array $reflectionParams, $params = [])
    {
        $args = [];

        for ($i = 0; $i < count($reflectionParams); $i++) {

            try {
                // if the parameter is not typed
                // ex: $n
                if (!$reflectionParams[$i]->hasType()) {
                    if ($params) {
                        $args[] = $params[$reflectionParams[$i]->getName()] ?? null;
                    } else {
                        $args[] = $_GET[$reflectionParams[$i]->getName()] ?? null;
                    }
                } else {
                    // if the parameter is typed
                    // ex: int $n
                    $reflectionType = $reflectionParams[$i]->getType();
                    $name = $reflectionType->getName();
                    // if the type is Builtin like a user defined class
                    if (!$reflectionType->isBuiltin()) {
                        // get a instance
                        $args[] = new $name;
                    } else {
                        // if exist in the given params array
                        if ($params && isset($params[$reflectionParams[$i]->getName()])) {
                            $value = $params[$reflectionParams[$i]->getName()];
                            $this->typedValue($name, $value);
                            $args[] = $value;
                        } else {
                            $value = $_GET[$reflectionParams[$i]->getName()] ?? "";
                            $this->typedValue($name, $value);
                            $args[] = $value;
                        }
                    }
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }

        return $args;
    }


    /**
     * return the request args for a route closure
     * 
     */
    private function closureArgs($closure, array $params = null)
    {
        try {
            $reflectionFunc = new ReflectionFunction($closure);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $reflectionParams = $reflectionFunc->getParameters();

        return $this->argsParameters($reflectionParams, $params);
    }


    /**
     * return the request args for a controller method
     * 
     */
    private function methodArgs($class, string $method, array $params = [])
    {
        $reflectionFunc = new ReflectionMethod($class, $method);
        $reflectionParams = $reflectionFunc->getParameters();

        return $this->argsParameters($reflectionParams, $params);
    }


    /**
     * dispatch the request
     * 
     */
    private function dispatch(Request $request, string $route, $action)
    {

        if ($request->isMethod("head") || $request->isMethod("GET")) {
            if (isset($this->hasParams)) {
                $params = $this->UrlParams($request->path(), $route);
            }
        } elseif ($request->isMethod("POST")) {
            $params = $_POST;
        }

        if (is_callable($action)) {
            // get the arguments for the closure action
            $args = $this->closureArgs($action, $params ?? null);
            // after that call the closure
            call_user_func_array($action, $args);
        } elseif (is_array($action)) {

            if (count($action) > 0) {
                // extract the controller and method
                list($controller, $method) = $action;
                $controller = new $controller;
                // get the method required parameters
                $args = $this->methodArgs($controller, $method, $params ?? []);
                // after that call the controller method
                call_user_func_array([$controller, $method], $args);
            }
        }

        exit;
    }

    private function UrlParams(string $path, string $route): array
    {
        $params = [];

        $pathParts = array_slice(explode('/', $path), 1);
        $routeParts = array_slice(explode('/', $route), 1);

        foreach ($routeParts as $key => $routePart) {

            if (strpos($routePart, '{') === 0) {
                $name = str_replace(["{", "}"], ["", ""], $routePart);

                if (strpos($name, "?")) {
                    $name = str_replace("?", "", $name);
                }

                if (strpos($pathParts[$key] ?? "", "+")) {
                    $pathParts[$key] = str_replace("+", " ", $pathParts[$key]);
                }

                if ($pathParts[$key] ?? null) {
                    $params[$name] = $pathParts[$key];
                }
            }
        }
        return $params;
    }


    /**
     * Build regular expression for the current route
     * 
     */
    private function makeRegexp(string $route): string
    {
        $r = $route;
        $this->opcional = false;

        if (preg_match_all("/(\ {[a-zA-Z0-9\?]+\})/", $route, $matches)) {
            // parameters types
            $types = Route::getParametersType()[$route] ?? '';

            for ($i = 0; $i < count($matches[1]); $i++) {
                $param = $matches[1][$i] ?? null;

                // replace the { } by white spaces
                $name = str_replace(["{", "}"], ["", ""], $param);

                // if is opcional
                if (strpos($name, "?")) {
                    $this->opcional = true;
                    $name = str_replace("?", "", $name);
                }
                // default pattern
                $pattern = "[a-zA-Z0-9\+]+";

                if (isset($types[$name])) {
                    $pattern = $types[$name];
                    if (isset($this->patterns[$pattern])) {
                        $pattern = $this->patterns[$pattern];
                    }
                }

                if ($this->opcional) {
                    $pattern = "({$pattern})?";
                }

                $route = str_replace($param, $pattern, $route);
            }

            $this->hasParams = true;
        }

        return $route;
    }




    /**
     * Define a action for not found requests
     * 
     * @return void
     */
    public function NotFoundAction(Closure $action)
    {
        $this->notFoundAction = $action;
    }



    /**
     * Run the router
     * 
     * 
     */
    public function run(Request $request, array $routes)
    {
        // the request method
        $method = strtolower($request->method());
        // the request path
        $path = $request->path();

        try {
            if (empty($routes))
                throw new Exception("No routes defined");
            // the route list for this method
            $list = $routes[$method] ?? null;

            if (is_null($list)) {
                throw new Exception("Not routes for method: {$method}");
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        // iterate all routes
        foreach ($list as $route => $action) {

            $regexp = $this->makeRegexp($route);

            // if the request have / at end insert it too to the regexp
            if ($regexp != "/") {
                $regexp = "@^$regexp$@";
            } else {
                $regexp = "@^/$@";
            }

            if (
                preg_match($regexp, $path) or
                preg_match($regexp, $path . '/')
            ) {
                return $this->dispatch($request, $route, $action);
            }
        }


        if (!isset($this->notFoundAction))
            die("NOT FOUND");
        else {
            $closure = $this->notFoundAction;
            $closure($request);
            exit;
        }
    }
}
