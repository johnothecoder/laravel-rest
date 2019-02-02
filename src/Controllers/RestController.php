<?php

namespace KyaSoftware\LaravelRest\Controllers;

use Illuminate\Routing\Controller;
use KyaSoftware\LaravelRest\Traits\FullRestCapabilities;

/**
 * Class RestController
 * @package KyaSoftware\LaravelRest\Controllers
 */
abstract class RestController extends Controller
{

    use FullRestCapabilities;

}