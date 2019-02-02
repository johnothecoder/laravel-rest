<?php

namespace KyaSoftware\LaravelRest\Traits;

use Illuminate\Http\Request;

/**
 * Trait DoesDestroy
 * @package KyaSoftware\LaravelRest\Traits
 */
trait DoesDestroy
{
    /**
     * @param Request $request
     * @param $modelId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $modelId)
    {
        $model = $this->getModelFromIdentifier($modelId);
        $this->verifyDestroyAccess($model);
        $model->delete();
        return response()->json(['message' => __('That resource has been deleted')]);
    }

    /**
     * @param $model
     */
    protected function verifyDestroyAccess($model){}

}