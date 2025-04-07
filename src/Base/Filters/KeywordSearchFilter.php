<?php

namespace Gomaa\Base\Base\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class KeywordSearchFilter implements Filter
{
    protected array $fields;
    public function __construct($fields = ['title'])
    {
        $this->fields = $fields;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
//        foreach ($this->fields as $oneField){
//            $query->orWhereRaw('LOWER(`'.$oneField.'`) LIKE ? ',['%'.strtolower($value).'%']);
//        }

        $query->where(function ($query) use ($value){
            foreach ($this->fields as $key => $field){
                if($key == 0)
                    $query->whereRaw("LOWER(`$field`) LIKE ? ",['%'.strtolower($value . '').'%']);
                else
                    $query->orWhereRaw("LOWER(`$field`) LIKE ? ",['%'.strtolower($value . '').'%']);
            }
        });
    }
}
