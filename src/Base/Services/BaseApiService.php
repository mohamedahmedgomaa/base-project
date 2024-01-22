<?php

namespace Gomaa\Test\Base\Services;

use Gomaa\Test\Base\Repositories\BaseApiRepository;
use Gomaa\Test\Base\Repositories\BaseRepository;
use Gomaa\Test\Base\Requests\BaseRequest;
use Gomaa\Test\Base\Responses\ApiResponse;
use Gomaa\Test\Base\Utils\AuthUtil;
use Gomaa\Test\Base\Utils\DataUtil;
use Illuminate\Http\JsonResponse;

/*
 * This class Will do:
 * - return CRUD response
 */
abstract class BaseApiService extends BaseService implements ServiceInterface
{
    use ApiResponse, AuthUtil, DataUtil;

    public $checkAuth = false;

    public $repository;

    /**
     * The attributes that want to filter by.
     */
    public $filters = [];

    /**
     * The attributes that want to insert or update.
     */
    public $fillable = [];


    /**
     * BaseApiService constructor.
     *
     * @param BaseApiRepository $repository
     */
    public function __construct(BaseApiRepository $repository)
    {
        $this->repository = $repository;

    }


    public function requestFilters(): array
    {
        $filters = $this->repository->baseFilters;
        foreach ($this->filters as $filter) {
            $filters[] = $filter;
        }
        return $filters;
    }


    /**
     * Get all items.
     *
     * @param BaseRequest $request
     * @return JsonResponse
     */
    public function index(BaseRequest $request): JsonResponse
    {
        $attributes = $request->only($this->requestFilters());
        return $this->execute(function () use ($attributes) {
            if ($result = $this->repository->getAll($attributes)) {
                if ($this->isExists($attributes, BaseRepository::pageNumber)) {
                    return $this->responseWithItemsAndMeta($result);
                } else {
                    return $this->responseWithData($result);
                }
            }

            return parent::responseErrorThereIsNoData();
        },$this->checkAuth);
    }

    /**
     * Get item by id.
     *
     * @param BaseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(BaseRequest $request,int $id): JsonResponse
    {
        return $this->execute(function () use ($id) {

            if ($result = $this->repository->getById($id))
                return $this->responseWithData($result);

            return parent::responseErrorThereIsNoData();
        },$this->checkAuth);
    }


    /**
     * Validate  data.
     * Store to DB if there are no errors.
     *
     * @param BaseRequest $request
     * @return JsonResponse
     */
    public function store(BaseRequest $request): JsonResponse
    {
        return $this->execute(function () use ($request) {

            $result =  $this->dbTransaction(function () use ($request) {
                return $this->repository->save($request->all());
            });

            if ($result) return $this->responseWithData($result);

            return $this->responseErrorThereIsNoData();
        },$this->checkAuth);
    }

    /**
     * Update  data  by id
     * Store to DB if there are no errors.
     *
     * @param BaseRequest $request
     * @param int $id
     * @param bool $restore
     * @return JsonResponse
     */
    public function update(BaseRequest $request, int $id, bool $restore = false): JsonResponse
    {
        $data = $request->all();
        return $this->execute(function () use ($id, $data, $restore) {
            $result =  $this->dbTransaction(function () use ($id, $data, $restore) {
                return $this->repository->updateById($id, $data, $restore);
            });

            if ($result) return $this->responseWithData($result);

            return $this->responseErrorThereIsNoData();
        },$this->checkAuth);
    }

    /**
     * Delete item by id.
     *
     * @param BaseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(BaseRequest $request, int $id): JsonResponse
    {
        return $this->execute(function () use ($id) {
            $result =  $this->dbTransaction(function () use ($id) {
                return $this->repository->deleteById($id);
            });

            if ($result) return $this->responseWithData($result);

            return $this->responseErrorThereIsNoData();
        },$this->checkAuth);
    }
}
