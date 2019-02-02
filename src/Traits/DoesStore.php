<?php

namespace KyaSoftware\LaravelRest\Traits;

use Illuminate\Http\Request;

/**
 * Trait DoesStore
 * @package KyaSoftware\LaravelRest\Traits
 */
trait DoesStore
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if(!empty($this->getStoreValidation())){
            $request->validate($this->getStoreValidation());
        }
        $class = $this->getModelClassName();
        $model = new $class();
        if(!empty($this->getStoreFillFields())){
            $model->fill($request->only($this->getStoreFillFields()));
        } else {
            $model->fill($request->all());
        }
        $model->save();
        return $this->returnSingleModel($model);
    }

    /**
     * @return array
     */
    protected function getStoreValidation() : array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getStoreFillFields() : array
    {
        return [];
    }

}