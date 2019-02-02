<?php
/**
 * Created by PhpStorm.
 * User: matthewjohnson
 * Date: 02/02/2019
 * Time: 23:17
 */

namespace KyaSoftware\LaravelRest\Traits;

/**
 * Trait DoesShow
 * @package KyaSoftware\LaravelRest\Traits
 */
trait DoesShow
{

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
    protected function verifyShowAccess($model){}

}