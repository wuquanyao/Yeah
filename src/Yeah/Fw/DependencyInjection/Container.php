<?php
namespace Yeah\Fw\DependencyInjection;

/**
 * Used for storing dependencies in a form of factories, and services. Object starts as a storage for service factories
 * which are invoked to create actual service. Factory is invoked only when needed. Service is instanced once in
 * an application lifetime and reference is reused every other time when needed.
 *
 * @property array $factories Collection of factory methods for instantiating
 * services
 * @property services $name Collection of already instantiated services
 */
class Container {

    private $map = array();
    private $object_map = array();

    public function register($record) {
        $this->map[$record['id']] = $record;
    }

    public function get($id) {
        if(!isset($this->map[$id])) {
            throw new Exception\ServiceUnavailableException($id);
        }

        if(isset($this->object_map[$id])) {
            return $this->object_map[$id];
        }

        $record = $this->map[$id];
        return $this->instantiate($record);
    }

    private function instantiate($record) {
        if(isset($record['value'])) {
            return $record['value'];
        };

        if(isset($record['class'])) {
            $object = $record['class']();
        };

        if(isset($record['factory'])) {
            $object = $record['factory']();
        };

        $dependencies = $object->getDependencies();

        foreach($dependencies as $setter => $id) {
            $object->$setter($this->get($id));
        }

        return $this->object_map[$record['id']] = $object;
    }

    public function instantiateFromClass($class) {
        return $this->instantiate(array(
            'class' => $class
        ));
    }
}
