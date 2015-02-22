<?php

namespace Yeah\Fw\Http;

/**
 * HTTP request implementation
 * 
 * @author David Cavar
 */
class Request {

    private $headers = array();
    private $parameters = array();
    private $method = null;
    private $requestBody = null;

    /**
     * 
     * @param array $options
     */
    public function __construct() {
        $this->retrieveRequestHeaders();
        $this->parseParameters();
    }

    /**
     * Magic method for accessing parameters by using get* pattern
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args) {
        if(strpos($method, 'get') == 0) {
            $key = strtolower(str_replace('get', '', $method));
            return $this->get($key);
        }
    }

    /**
     * Parses request headers
     */
    public function retrieveRequestHeaders() {
        foreach($_SERVER as $key => $value) {
            $value = filter_var($_SERVER[$key]);
            $key = strtolower(str_replace(array('-', '_', 'HTTP'), '', $key));
            $this->headers[$key] = $value;
        }
    }

    /**
     * Retrieves request parameter
     * 
     * @param string $key
     * @return string|boolean
     */
    public function getParameter($key) {
        if(isset($this->parameters[$key])) {
            return $this->parameters[$key];
        } else {
            return false;
        }
    }

    /**
     * Overwrites default request parameter
     * 
     * @param string $key
     * @param mixed $value
     */
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }

    /**
     * Retrieves all request parameters
     * 
     * @return array
     */
    public function getAllParameters() {
        return $this->parameters;
    }

    /**
     * Parses request parameters
     */
    public function parseParameters() {
        $this->parseGetParameters();
        $this->parsePostParameters();
    }

    /**
     * Parses GET parameters
     */
    public function parseGetParameters() {
        $params = str_replace('?', '/', $this->_getRequestUri());
        $params = str_replace('=', '/', $params);
        $params = str_replace('&', '/', $params);
        $params = explode('/', $params);
        array_shift($params);
        if(strpos($params[0], '.php') != FALSE) {
            array_shift($params);
        }
        $this->parameters['controller'] = isset($params[0]) ? $params[0] : '';
        $this->parameters['action'] = isset($params[1]) ? $params[1] : NULL;
        for($i = 0; $i < (count($params)); $i++) {
            $next = $i + 1;
            $this->parameters[$params[$i]] = isset($params[$next]) ? $params[$next] : NULL;
        }
    }

    /**
     * Parses POST parameters
     */
    public function parsePostParameters() {
        foreach($_POST as $key => $value) {
            $value = filter_var($_POST[$key]);
            $this->parameters[$key] = $value;
        }
    }

    /**
     * Gets request POST content
     * 
     * @return string
     */
    public function getRequestBody() {
        if($this->getContentLength() == 0) {
            return '';
        }
        if($this->requestBody) {
            return $this->requestBody;
        }
        $this->requestBody = file_get_contents('php://input');
        return $this->requestBody;
    }

    /**
     * Retrieves header value
     * 
     * @param string $key
     * @return string|boolean
     */
    public function get($key) {
        if(isset($this->headers[$key])) {
            return $this->headers[$key];
        } else {
            return false;
        }
    }

    /**
     * Sets header value
     * 
     * @param set $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $this->headers[$key] = $value;
    }

    /**
     * Identifies HTTP request method (GET, POST, PUT, DELETE)
     * 
     * @return string
     */
    public function getRequestMethod() {
        return filter_var($_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_STRING);
    }

    /**
     * Return request uri with query string
     * 
     * @return string
     */
    private function _getRequestUri() {
        return filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_STRING);
    }
    
    /**
     * Return request uri without query string
     * 
     * @return string
     */
    public function getRequestUri() {
        return explode('?', $this->_getRequestUri())[0];
    }
    
}
