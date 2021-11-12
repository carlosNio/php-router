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

class ParametersBag implements Collection
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


    // other functions 

    /**
     * return a boolean if a stored value is valid for a filter
     * 
     * @return bool
     */
    public function isValidFor(string $key, $filter)
    {
        $data = $this->get($key);
        return (bool) filter_var($data, $filter);
    }

    /**
     * return a the string value of a stored value
     * 
     * @return string
     */
    public function get_string(string $key)
    {
        return (string) $this->get($key, '');
    }

    /**
     * return only the integer value from a stored value , deleting others characters
     * 
     * @return integer
     */
    public function get_integer(string $key)
    {
        return (int) preg_replace("/[^0-9]+/", '', $this->get($key, ''));
    }


    /**
     * return float value from a stored value
     * 
     * @return float
     */
    public function get_float(string $key)
    {
        return (float) $this->get($key, '');
    }


    /**
     * return only the alfa characters value from a stored value , deleting others characters
     * 
     * @return string
     */
    public function get_alfa(string $key)
    {
        return preg_replace("/[^A-Za-z]+/", '', $this->get($key, ''));
    }


    /**
     * return only the alfanumeric characters value from a stored value , deleting others characters
     * 
     * @return string
     */
    public function get_alfanum(string $key)
    {
        return preg_replace("/[^A-Za-z0-9]+/", '', $this->get($key, ''));
    }
}
