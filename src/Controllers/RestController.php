<?php

namespace KyaSoftware\LaravelRest\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class RestController extends Controller
{

    public function list(Request $request)
    {
        $class = $this->getModelClassName();
        $query = $class::select($this->getDefaultSelect());
        $this->handleFiltering($request, $query);
        $this->handleSearch($request, $query);

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

    public function show(Request $request, $modelId)
    {

    }

    public function store(Request $request)
    {

    }

    public function update(Request $request, $modelId)
    {

    }

    public function destroy(Request $request, $modelId)
    {

    }

    protected function usePagination() : bool
    {
        return true;
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

    abstract protected function getModelClassName() : string;

}