<?php

namespace Nio\Router\Interfaces;


/**
 * Collection interface
 * 
 * @author Carlos bumba git:CarlosNio
 */

interface Collection
{
    /**
     * return the number of items
     * @return int
     */
    public function count();

    /**
     * return all the items
     * @return array
     */
    public function all();

    /**
     * return a item
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * set a new item ti the collection
     * @return void
     */
    public function set(string $key, $value);

    /**
     * test if a item are in collection
     * @return bool
     */
    public function has(string $key);

    /**
     * return all items keys
     * 
     * @return array
     */
    public function keys();

    /**
     * return all items values
     * 
     * @return array
     */
    public function values();

    /**
     * delete a collection item
     * 
     * @return void
     */
    public function delete(string $key);
}
