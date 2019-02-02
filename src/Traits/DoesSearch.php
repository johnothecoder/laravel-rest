<?php

namespace KyaSoftware\LaravelRest\Traits;

use Illuminate\Http\Request;

trait DoesSearch
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

}