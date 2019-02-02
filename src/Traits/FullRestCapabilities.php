<?php

namespace KyaSoftware\LaravelRest\Traits;

/**
 * Trait FullRestCapabilities
 * @package KyaSoftware\LaravelRest\Traits
 */
trait FullRestCapabilities
{
    use HasRestModel, DoesDestroy, DoesPatch, DoesPut, DoesSearch, DoesStore, DoesShow;
}