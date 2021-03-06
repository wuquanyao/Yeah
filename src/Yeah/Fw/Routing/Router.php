<?php

namespace Yeah\Fw\Routing;

/**
 * Maps request URI to appropriate controller and action
 * 
 * @author David Cavar
 */
class Router {

    private static $routes = array();

    /**
     * Maps URI to route
     * 
     * @param string $route URI key under which to map specified route
     * @param type $params
     */
    public static function add($route, $params = array()) {
        self::$routes[$route] = $params;
    }

    /**
     * Retrieves route config
     * 
     * @param string $route URI key under which to map specified route
     */
    public static function get($route) {
        if(!isset(self::$routes[$route])) {
            return false;
        }
        return self::$routes[$route];
    }

    /**
     * Removes route under specified URI key
     * 
     * @param string $route URI key
     */
    public static function remove($route) {
        unset(self::$routes[$route]);
    }

    /**
     * Handles route request
     * 
     * @param \Yeah\Fw\Http\Request $request HTTP request object
     * @return RouteInterface
     */
    public function handle(\Yeah\Fw\Http\Request $request) {
        foreach(self::$routes as $pattern => $options) {
            $routeRequest = new $options['route_request_handler']();
            $options['pattern'] = $pattern;
            $route = $routeRequest->handle($options, $request);
            if($route) {
                return $route;
            }
        }
        if(!$this->matchDynamic($request)) {
            throw new \Yeah\Fw\Http\Exception\NotFoundHttpException();
        }
        $class = '\\' . ucfirst($request->get('controller')) . 'Controller';
        if(!class_exists($class)) {
            throw new \Yeah\Fw\Http\Exception\NotFoundHttpException();
        }
        $route = new \Yeah\Fw\Routing\Route\Route();
        $route->setAction($request->get('action'));
        $controller = new $class();
        $route->setController($controller);
        $route->setSecure(false);
        return $route;
    }

    function matchDynamic(\Yeah\Fw\Http\Request $request) {
        $matches = array();
        if(preg_match('/\/(.*)\/(.*)/', $request->getRequestUri(), $matches)) {
            $request->set('controller', $matches[1]);
            $request->set('action', $matches[2]);
            return true;
        }
        return false;
    }
}
