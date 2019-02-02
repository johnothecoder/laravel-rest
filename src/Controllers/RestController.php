<?php

namespace KyaSoftware\LaravelRest\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use KyaSoftware\LaravelRest\Handlers\Search\SearchHandler;

abstract class RestController extends Controller
{

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

    protected function buildModelLinks(&$model)
    {
        $model->links = [];
    }

    protected function handleFiltering(Request $request, &$query)
    {
        foreach($this->getFilterFields() as $field){
            if($request->$field === null){
                continue;
            }
            $query->where($field, '=', $request->$field);
        }
    }

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

    protected function handleOrdering(Request $request, &$query)
    {
        $orderByField = $this->getOrderByField();
        $orderField = $this->getOrderField();
        $query->orderBy(
            $request->$orderByField ?? $this->getDefaultOrderBy(),
            $request->$orderField ?? $this->getDefaultOrder()
        );
    }

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

    protected function handleAdditionalFiltering(Request $request, &$query){}

    public function show(Request $request, $modelId)
    {
        $model = $this->getModelFromIdentifier($modelId);
        $this->verifyShowAccess($model);
        return $this->returnSingleModel($model);
    }

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

    protected function returnSingleModel($model)
    {
        $model->refresh();
        $this->buildModelLinks($model);
        return response()->json($model);
    }

    public function destroy(Request $request, $modelId)
    {
        $model = $this->getModelFromIdentifier($modelId);
        $this->verifyDeleteAccess($model);
        $model->delete();
        return response()->json(['message' => __('That resource has been deleted')]);
    }

    protected function getStoreValidation() : array
    {
        return [];
    }

    protected function getStoreFillFields() : array
    {
        return [];
    }

    protected function getPatchValidation() : array
    {
        return [];
    }

    protected function getModelFromIdentifier($identifier)
    {
        $class = $this->getModelClassName();
        $model = $class::find($identifier);
        if(empty($model)){
            abort(404);
        }
        return $model;
    }

    protected function usePagination() : bool
    {
        return true;
    }

    protected function getTrashField() : string
    {
        return 'trash';
    }

    protected function getOrderField() : string
    {
        return 'order';
    }

    protected function getOrderByField() : string
    {
        return 'order_by';
    }

    protected function getDefaultOrder() : string
    {
        return 'DESC';
    }

    protected function getDefaultOrderBy() : string
    {
        return 'updated_at';
    }

    protected function getRecordsPerPage() : int
    {
        return 25;
    }

    protected function getSearchField() : string
    {
        return 'search';
    }

    protected function getDefaultSelect() : string
    {
        return '*';
    }

    protected function getPerPageField() : string
    {
        return 'per_page';
    }

    protected function getModelInstanceFromId(int $id)
    {
        $class = $this->getModelClassName();
        $record = $class::find($id);
        if(empty($record)){
            abort(404);
        }
        return $record;
    }

    protected function getFilterFields() : array
    {
        return [];
    }

    protected function getFluffyFields() : array
    {
        return [];
    }

    protected function verifyIndexAccess(){}

    protected function verifyShowAccess($model){}

    protected function verifyPatchAccess($model){}

    protected function verifyPutAccess($model){}

    protected function verifyDeleteAccess($model){}

    protected function getPatchableFields() : array
    {
        return [];
    }

    protected function getPutFields() : array
    {
        return [];
    }

    abstract protected function getModelClassName() : string;

}