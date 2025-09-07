<?php

namespace Gomaa\Base\Base\Repositories;

use Gomaa\Base\Base\Utils\AuthUtil;
use Gomaa\Base\Base\Utils\DataUtil;

/*
 * This class Will do:
 * - get result with filters (date, sort, withTrashed, paginate)
 * - add create by
 * - add update by
 */
abstract class BaseRepository
{
    use AuthUtil, DataUtil;

    const pageNumber = "page";
    const perPage = "perPage";
    const defaultPerPage = 20;

    const dateFrom = "dateFrom";
    const dateTo = "dateTo";
    const defaultDateColumn = "created_at";

    const sortBy = "sortBy";
    const sortDesc = "sortDesc";

    const withTrashed = "withTrashed";

    /**
     * The attributes that want to insert or update.
     */
    public $baseFilters = [
        self::pageNumber, self::perPage,
        self::dateFrom, self::dateTo,
        self::sortBy, self::sortDesc,
        self::withTrashed, "search"
    ];


    public function result(array $attributes, $query, $table, $orderByLatest = true)
    {

        if ($value = $this->isExists($attributes, self::dateFrom)) {
            $query->whereDate($table . "." . self::defaultDateColumn, '>=', $value);
        }

        if ($value = $this->isExists($attributes, self::dateTo)) {
            $query->whereDate($table . "." . self::defaultDateColumn, '<=', $value);
        }

        if ($value = $this->isExists($attributes, self::sortBy)) {
            $query->orderBy("$table.$value", $attributes['sortDesc'] == "true" ? "DESC" : 'ASC');
        }

        if ($value = $this->isExists($attributes, self::withTrashed)) {
            if ($value == 1)
                $query->withTrashed();
            else if ($value == 2)
                $query->onlyTrashed();
        }

        if ($orderByLatest)
            $query = $query->latest();


        if ($this->isExists($attributes, self::pageNumber)) {
            $requestedPage = (int) $attributes[self::pageNumber];
            $perPage = $this->perPage($attributes);

            $records = $query->paginate($perPage, ['*'], 'page', $requestedPage);

            if ($requestedPage > $records->lastPage()) {
                $records = $query->paginate($perPage, ['*'], 'page', $requestedPage);

                $records->setCollection(collect([]));
            }

            return $records;
        }
        return $query->get();
    }


    public function perPage($attributes)
    {
        if ($value = $this->isExists($attributes, self::perPage))
            return $value;
        return self::defaultPerPage;
    }


    public function createdBy(&$object)
    {
        $object['created_by'] = $this->userId();
    }

    public function updatedBy(&$object)
    {
        $object['updated_by'] = $this->userId();
    }
}
