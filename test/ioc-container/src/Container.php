<?php


namespace App;


class Container
{
    static $instance = null;
    private $bindings = [];

    public static function getInstance()
    {
        if (!is_null(static::$instance)) {
            return static::$instance;
        }

        static::$instance = new static;

        return static::$instance;
    }

    public function bind($key, $resolver)
    {
        $this->bindings[$key] = $resolver;
    }

    public function make($key)
    {
        if (!array_key_exists($key, $this->bindings)) {
            return $key;
        }

        if ($this->bindings[$key] instanceof \Closure) {
            return call_user_func($this->bindings[$key]);
        }

        return $this->build($this->bindings[$key]);
    }

    private function build($class)
    {
        $reflectionClass = new \ReflectionClass($class);

        if ($reflectionClass->isFinal()) {
            return $reflectionClass->newInstance();
        }

        $constructor = $reflectionClass->getConstructor();
        $parameters = $constructor->getParameters();

        $arguments = [];

        foreach ($parameters as $parameter) {
            $arguments[] = $this->build($parameter->getClass()->getName());
        }

       return $reflectionClass->newInstanceArgs($arguments);
    }
}