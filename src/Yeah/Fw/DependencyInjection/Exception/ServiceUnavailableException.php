<?php
namespace Yeah\Fw\DependencyInjection\Exception;

class ServiceUnavailableException extends \Exception {

    public function __construct($id) {
        $message = sprintf('Service %s is not registered.', $id);
        parent::__construct($message);
    }
}