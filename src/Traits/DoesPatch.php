<?php

namespace KyaSoftware\LaravelRest\Traits;

use Illuminate\Http\Request;

/**
 * Trait DoesPatch
 * @package KyaSoftware\LaravelRest\Traits
 */
trait DoesPatch
{
    /**
     * @param Request $request
     * @param $modelId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function patch(Request $request, $modelId)
    {
        if(empty($this->getPatchableFields())){
            Throw new \Exception(__("You have not set any PATCH fields"));
        }
        if(!empty($this->getPatchValidation())){
            $request->validate($this->getPatchValidation());
        }
        $model = $this->getModelFromIdentifier($modelId);
        $this->verifyPatchAccess($model);
        foreach($this->getPatchableFields() as $field){
            $model->$field = $request->$field;
        }
        $model->save();
        return $this->returnSingleModel($model);
    }

    /**
     * @return array
     */
    protected function getPatchValidation() : array
    {
        return [];
    }

    /**
     * @param $model
     */
    protected function verifyPatchAccess($model){}

    /**
     * @return array
     */
    protected function getPatchableFields() : array
    {
        return [];
    }

}