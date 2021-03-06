<?php

namespace Yeah\Fw\Application;

/**
 * Used for storing dependencies in a form of factories, and services. Object starts as a storage for service factories
 * which are invoked to create actual service. Factory is invoked only when needed. Service is instanced once in
 * an application lifetime and reference is reused every other time when needed.
 *
 * @property array $factories Collection of factory methods for instantiating
 * services
 * @property services $name Collection of already instantiated services
 */
class DependencyContainer {

    private $factories = array();
    private $services = array();

    /**
     * Sets factory method for service
     *
     * @param string $key
     * @param Closure $factory
     */
    public function set($key, $factory) {
        $this->factories[$key] = $factory;
        if(isset($this->services[$key])) {
            unset($this->services[$key]);
        }
    }

    /**
     * Retrieves instantiated service
     *
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        if(!isset($this->factories[$key])) {
            return null;
        }
        if(!isset($this->services[$key])) {
            $this->services[$key] = $this->factories[$key]();
        }
        return $this->services[$key];
    }

}
