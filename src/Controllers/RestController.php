<?php

namespace KyaSoftware\LaravelRest\Controllers;

use Illuminate\Routing\Controller;
use KyaSoftware\LaravelRest\Traits\DoesDestroy;
use KyaSoftware\LaravelRest\Traits\DoesPatch;
use KyaSoftware\LaravelRest\Traits\DoesPut;
use KyaSoftware\LaravelRest\Traits\DoesSearch;
use KyaSoftware\LaravelRest\Traits\DoesStore;
use KyaSoftware\LaravelRest\Traits\HasRestModel;

/**
 * Class RestController
 * @package KyaSoftware\LaravelRest\Controllers
 */
abstract class RestController extends Controller
{

    use HasRestModel, DoesSearch, DoesStore, DoesPut, DoesPatch, DoesDestroy;

}