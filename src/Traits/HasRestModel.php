<?php

namespace KyaSoftware\LaravelRest\Traits;

use Illuminate\Http\Request;

/**
 * Trait HasModel
 * @package KyaSoftware\LaravelRest\Traits
 */
trait HasRestModel
{

    /**
     * @return string
     */
    abstract protected function getModelClassName() : string;

    /**
     * @param Request $request
     * @param $modelId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $modelId)
    {
        $model = $this->getModelFromIdentifier($modelId);
        $this->verifyShowAccess($model);
        return $this->returnSingleModel($model);
    }

    /**
     * @param $model
     */
    protected function buildModelLinks(&$model)
    {
        $model->links = [];
    }

    /**
     * @param $model
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnSingleModel($model)
    {
        $model->refresh();
        $this->buildModelLinks($model);
        return response()->json($model);
    }

    /**
     * @param $identifier
     * @return mixed
     */
    protected function getModelFromIdentifier($identifier)
    {
        $class = $this->getModelClassName();
        $model = $class::find($identifier);
        if(empty($model)){
            abort(404);
        }
        return $model;
    }

    /**
     * @param $model
     */
    protected function verifyShowAccess($model){}

    /**
     * @param int $id
     * @return mixed
     */
    protected function getModelInstanceFromId(int $id)
    {
        $class = $this->getModelClassName();
        $record = $class::find($id);
        if(empty($record)){
            abort(404);
        }
        return $record;
    }

}