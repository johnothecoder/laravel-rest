<?php

namespace KyaSoftware\LaravelRest\Traits;

use Illuminate\Http\Request;

/**
 * Trait DoesPut
 * @package KyaSoftware\LaravelRest\Traits
 */
trait DoesPut
{

    /**
     * @param Request $request
     * @param $modelId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function put(Request $request, $modelId)
    {
        if(empty($this->getPutFields())){
            Throw new \Exception(__("You have not set any PUT fields"));
        }
        $model = $this->getModelFromIdentifier($modelId);
        $this->verifyPutAccess($model);
        if(!empty($this->getPutValidation())){
            $request->validate($this->getPutValidation());
        }
        $model->fill($request->only($this->getPutFields()));
        $model->save();
        return $this->returnSingleModel($model);
    }

    /**
     * @return array
     */
    protected function getPutValidation() : array
    {
        return [];
    }

    /**
     * @param $model
     */
    protected function verifyPutAccess($model){}

    /**
     * @return array
     */
    protected function getPutFields() : array
    {
        return [];
    }

}