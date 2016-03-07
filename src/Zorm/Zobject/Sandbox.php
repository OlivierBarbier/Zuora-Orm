<?php

namespace OlivierBarbier\Zorm\Zobject;

class Sandbox
{
    /**
     * @param string $objectName
     * @param array  $fields
     *
     * @return \OlivierBarbier\Zorm\Zobject\Base
     */
    protected function make($objectName, $fields)
    {
        $zobject = app('OlivierBarbier\Zorm\Zobject\\'.$objectName);

        $zobject->fill($fields);

        $create = $zobject->create();

        return $zobject->find($create->result->Id);
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (starts_with($method, 'make')) {
            return $this->dynamicMake($method, $parameters);
        }

        $className = get_class($this);

        throw new \BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return \OlivierBarbier\Zorm\Zobject\Base
     */
    public function dynamicMake($method, $parameters)
    {
        $objectName = substr($method, 4);

        $fields = (object) $parameters[0];

        return $this->make($objectName, $fields);
    }
}
