<?php

namespace KyaSoftware\LaravelRest\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class RestController
 * @package KyaSoftware\LaravelRest\Controllers
 */
abstract class RestController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $class = $this->getModelClassName();
        $this->verifyIndexAccess();
        $query = $class::select($this->getDefaultSelect());
        $this->handleFiltering($request, $query);
        $this->handleSearch($request, $query);
        $this->handleAdditionalFiltering($request, $query);
        $this->handleOrdering($request, $query);
        $this->handleTrash($request, $query);
        if($this->usePagination() === true){
            $perPageField = $this->getPerPageField();
            $results = $query->paginate($request->$perPageField ?? 25)->appends(request()->query());
        } else {
            $results = $query->get();
        }
        foreach($results as &$result){
            $this->buildModelLinks($result);
        }
        return response()->json($results);
    }

    /**
     * @param $model
     */
    protected function buildModelLinks(&$model)
    {
        $model->links = [];
    }

    /**
     * @param Request $request
     * @param $query
     */
    protected function handleFiltering(Request $request, &$query)
    {
        foreach($this->getFilterFields() as $field){
            if($request->$field === null){
                continue;
            }
            $query->where($field, '=', $request->$field);
        }
    }

    /**
     * @param Request $request
     * @param $query
     */
    protected function handleSearch(Request $request, &$query)
    {
        $searchField = 'search';
        $fluffyFields = $this->getFluffyFields();
        $searchTerm = $request->$searchField;
        if(!empty($fluffyFields) && !empty($searchTerm)){
            $query->where(function($query) use($fluffyFields, $searchTerm){
                foreach($fluffyFields as $field){
                    $query->orWhere($field, 'LIKE', "%$searchTerm%");
                }
            });
        }
    }

    /**
     * @param Request $request
     * @param $query
     */
    protected function handleOrdering(Request $request, &$query)
    {
        $orderByField = $this->getOrderByField();
        $orderField = $this->getOrderField();
        $query->orderBy(
            $request->$orderByField ?? $this->getDefaultOrderBy(),
            $request->$orderField ?? $this->getDefaultOrder()
        );
    }

    /**
     * @param Request $request
     * @param $query
     */
    protected function handleTrash(Request $request, $query)
    {
        $model = $this->getModelClassName();
        $uses = class_uses($model);
        $softDeleted = 'Illuminate\Database\Eloquent\SoftDeletes';
        if(!in_array($softDeleted, $uses)){
            return;
        }
        $trashField = $this->getTrashField();
        if($request->$trashField == true){
            $query->onlyTrashed();
        }
    }

    /**
     * @param Request $request
     * @param $query
     */
    protected function handleAdditionalFiltering(Request $request, &$query){}

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
     * @param Request $request
     * @param $modelId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $modelId)
    {
        $model = $this->getModelFromIdentifier($modelId);
        $this->verifyDeleteAccess($model);
        $model->delete();
        return response()->json(['message' => __('That resource has been deleted')]);
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
    protected function getPutValidation() : array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getPatchValidation() : array
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
     * @return bool
     */
    protected function usePagination() : bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getTrashField() : string
    {
        return 'trash';
    }

    /**
     * @return string
     */
    protected function getOrderField() : string
    {
        return 'order';
    }

    /**
     * @return string
     */
    protected function getOrderByField() : string
    {
        return 'order_by';
    }

    /**
     * @return string
     */
    protected function getDefaultOrder() : string
    {
        return 'DESC';
    }

    /**
     * @return string
     */
    protected function getDefaultOrderBy() : string
    {
        return 'updated_at';
    }

    /**
     * @return int
     */
    protected function getRecordsPerPage() : int
    {
        return 25;
    }

    /**
     * @return string
     */
    protected function getSearchField() : string
    {
        return 'search';
    }

    /**
     * @return string
     */
    protected function getDefaultSelect() : string
    {
        return '*';
    }

    /**
     * @return string
     */
    protected function getPerPageField() : string
    {
        return 'per_page';
    }

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

    /**
     * @return array
     */
    protected function getFilterFields() : array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getFluffyFields() : array
    {
        return [];
    }

    /**
     * Verify that this user can access the index/search isting
     */
    protected function verifyIndexAccess(){}

    /**
     * @param $model
     */
    protected function verifyShowAccess($model){}

    /**
     * @param $model
     */
    protected function verifyPatchAccess($model){}

    /**
     * @param $model
     */
    protected function verifyPutAccess($model){}

    /**
     * @param $model
     */
    protected function verifyDeleteAccess($model){}

    /**
     * @return array
     */
    protected function getPatchableFields() : array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getPutFields() : array
    {
        return [];
    }

    /**
     * @return string
     */
    abstract protected function getModelClassName() : string;

}