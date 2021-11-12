<?php

namespace Nio\Router;

use Nio\Router\Interfaces\Collection;


/**
 * ParametersBag
 * 
 * this class store request data like POST , GET , FILES
 * 
 * @author Carlos Bumba git:CarlosNio
 */

class HeadersBag implements Collection
{
    private $bag;

    public function __construct(array $bag)
    {
        $this->bag = $bag;
    }

    // collection implementations

    public function count()
    {
        return count($this->bag);
    }

    public function all()
    {
        return $this->bag;
    }

    public function get(string $key, $default = null)
    {
        if (!$this->has($key)) return $default;
        return $this->bag[$key];
    }

    public function set(string $key, $value)
    {
        $this->bag[$key] = $value;
    }

    public function has(string $key)
    {
        return array_key_exists($key, $this->bag);
    }

    public function keys()
    {
        return array_keys($this->bag);
    }

    public function values()
    {
        return array_values($this->bag);
    }

    public function delete(string $key)
    {
        if ($this->has($key)) unset($this->bag[$key]);
    }

}
