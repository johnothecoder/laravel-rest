<?php

namespace KyaSoftware\LaravelRest;

use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Class Route
 * @package KyaSoftware\LaravelRest
 */
class Route
{

    /**
     * @param $modelRoute
     * @param $route
     * @param $controller
     * @param array $methods
     */
    public static function register($modelRoute, $route, $controller, $methods = ['index','show','patch','put','destroy','store'])
    {

        // If we want it, register the listing/search method
        if(in_array('index', $methods)){
            RouteFacade::get($route, $controller . "@index");
        }

        // If we want it, register the store method
        if(in_array('store', $methods)){
            RouteFacade::post($route, $controller . "@store");
        }

        // Generate a usable model route
        $usableModelRoute = $route . '/{' . $modelRoute . '}';

        // If we want it, register the show route
        if(in_array('show', $methods)){
            RouteFacade::get($usableModelRoute, $controller . "@show");
        }

        // If we want it, register the patch route
        if(in_array('patch', $methods)){
            RouteFacade::patch($usableModelRoute, $controller . "@patch");
        }

        // If we want it, register the put route
        if(in_array('put', $methods)){
            RouteFacade::put($usableModelRoute, $controller . "@put");
        }

        // If we want it, register the destroy route
        if(in_array('destroy', $methods)){
            RouteFacade::delete($usableModelRoute, $controller . "@destroy");
        }

    }

}