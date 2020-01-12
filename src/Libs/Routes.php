<?php

namespace Iblues\AnnotationTestUnit\Libs;

use Illuminate\Support\Facades\Route;

class Routes
{
    /**
     * @return \Illuminate\Routing\RouteCollection
     * @author Blues
     */
    static function getRoutes()
    {
        $routes = Route::getRoutes();
        return $routes = self::parseRoutes($routes);
    }


    static function parseRoutes($apiRoutes)
    {
        $data = [];
        foreach ($apiRoutes as $route) {
            $url = $route->uri;
            $method = $route->methods[0];
            $as = @$route->action['controller'];
            $data[] = ['path' => $as, 'method' => $method, 'url' => $url];
        }
        return $data;
    }
}