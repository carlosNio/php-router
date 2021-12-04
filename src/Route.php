<?php

namespace Nio\Router;

final class Route
{
    private static $map;
    private static $types;

    /**
     * Register a new route
     * 
     * @return void
     */
    private static function register(string $method, string $route, $action, $where)
    {
        self::$map[$method][$route] = $action;

        if (!empty($where)) {
            self::$types[$route] = $where;
        }
    }

    /**
     * return all routers defined
     * 
     * @return array
     */
    public static function getRoutes()
    {
        return self::$map ?? [];
    }


    /**
     * return all routers defined
     * 
     * @return array
     */
    public static function getParametersType()
    {
        return self::$types ?? [];
    }


    /**
     * define a type for route params
     * 
     * 
     * @return this
     */


    // REQUEST METHODS

    /**
     * register a new GET route
     * 
     * @return void
     */
    public static function get(string $route, $action, array $where = [])
    {
        self::register('get', $route, $action, $where);
    }


    /**
     * register a new HEAD route
     * 
     * @return void
     */
    public static function head(string $route, $action, array $where = [])
    {
        self::register('head', $route, $action, $where);
    }


    /**
     * register a new POST route
     * 
     * @return void
     */
    public static function post(string $route, $action, array $where = [])
    {
        self::register('post', $route, $action, $where);
    }


    /**
     * register a new PUT route
     * 
     * @return void
     */
    public static function put(string $route, $action, array $where = [])
    {
        self::register('put', $route, $action, $where);
    }

    /**
     * register a new PATCH route
     * 
     * @return void
     */
    public static function patch(string $route, $action, array $where = [])
    {
        self::register('patch', $route, $action, $where);
    }


    /**
     * register a new DELETE route
     * 
     * @return void
     */
    public static function delete(string $route, $action, array $where = [])
    {
        self::register('delete', $route, $action, $where);
    }


    /**
     * register a new route in all request methods
     * 
     * @return void
     */
    public static function any(string $route, $action, array $where = [])
    {
        $methods = ['get', 'head', 'post', 'put' , 'patch', 'delete'];
        foreach ($methods as $method) {
            self::register($method, $route, $action, $where);
        }
    }


    /**
     * register a new route in specified methods
     * 
     * @return void
     */
    public static function match(array $methods, string $route, $action, array $where = [])
    {
        foreach ($methods as $method) {
            self::register($method, $route, $action, $where);
        }
    }
    

    
    /** 
     * register a REST resource
     * 
     * 
     * @return void
     */

    public function resource(string $name, string $classname, bool $auth = false, $level = null)
    {
        //index
        $this->match(['get', 'head'], "/{$name}", [$classname, 'index']);
        //store
        $this->post("/{$name}", [$classname, 'store']);
        //create
        $this->match(['get', 'head'], "/{$name}/create", [$classname, 'create']);
        //show
        $this->get("/{$name}/{id}", [$classname, 'show'] , ['id' => 'uuid']);
        $this->head("/{$name}/{id}", [$classname, 'show'], ['id' => 'uuid']);
        //edit        
        $this->get("/{$name}/{id}/edit", [$classname, 'edit'], ['id' => 'uuid']);
        $this->head("/{$name}/{id}/edit", [$classname, 'edit'], ['id' => 'uuid']);
        //update
        $this->put("/{$name}/{id}", [$classname, 'update'], ['id' => 'uuid']);
        $this->patch("/{$name}/{id}", [$classname, 'update'], ['id' => 'uuid']);
        //destroy
        $this->delete("/{$name}/{id}", [$classname, 'destroy'], ['id' => 'uuid']);
    }
}
