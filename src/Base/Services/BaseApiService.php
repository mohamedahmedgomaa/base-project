<?php

namespace Gomaa\Base\Base\Services;

use Gomaa\Base\Base\Repositories\BaseApiRepository;
use Gomaa\Base\Base\Repositories\BaseRepository;
use Gomaa\Base\Base\Requests\BaseRequest;
use Gomaa\Base\Base\Responses\ApiResponse;
use Gomaa\Base\Base\Utils\AuthUtil;
use Gomaa\Base\Base\Utils\DataUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/*
 * This class Will do:
 * - return CRUD response (now with DTO + Mapper)
 */
abstract class BaseApiService extends BaseService implements ServiceInterface
{
    use ApiResponse, AuthUtil, DataUtil;

    public bool $checkAuth = false;

    protected BaseApiRepository $repository;

    /**
     * Define DTO + Mapper for each child service.
     */
    protected string $dtoClass;
    protected string $mapperClass;

    /**
     * The attributes that want to filter by.
     */
    public array $filters = [];

    /**
     * The attributes that want to insert or update.
     */
    public array $fillable = [];

    public function __construct(BaseApiRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Build filters for requests.
     */
    public function requestFilters(): array
    {
        $filters = $this->repository->baseFilters;
        foreach ($this->filters as $filter) {
            $filters[] = $filter;
        }
        return $filters;
    }

    /**
     * Convert model → DTO.
     */
    protected function toDto($model)
    {
        if (!$model) return null;

        $mapper = app($this->mapperClass);
        $dto    = new $this->dtoClass();
        $mapper->modelToDto($model, $dto);

        return $dto;
    }

    /**
     * Convert collection of models → DTOs.
     */
    protected function toDtos($models): Collection
    {
        if ($models instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $models = $models->items();
        }

        return collect($models)->map(fn($model) => $this->toDto($model));
    }

    /**
     * Get all items.
     */
    public function index(BaseRequest $request): JsonResponse
    {
        $attributes = $request->only($this->requestFilters());

        return $this->execute(function () use ($attributes) {
            if ($result = $this->repository->getAll($attributes)) {

                if ($this->isExists($attributes, BaseRepository::pageNumber)) {
                    return $this->responseWithItemsAndMeta($result);
                }

                $data = $this->toDtos($result);
                return $this->responseWithData($data);
            }

            return parent::responseErrorThereIsNoData();
        }, $this->checkAuth);
    }


    /**
     * Get item by id.
     */
    public function show(BaseRequest $request, int $id): JsonResponse
    {
        return $this->execute(function () use ($id) {
            if ($result = $this->repository->getById($id)) {
                return $this->responseWithData($this->toDto($result));
            }

            return parent::responseErrorThereIsNoData();
        }, $this->checkAuth);
    }

    /**
     * Store data.
     */
    public function store(BaseRequest $request): JsonResponse
    {
        return $this->execute(function () use ($request) {
            $result = $this->dbTransaction(fn () => $this->repository->save($request->all()));

            if ($result) return $this->responseWithData($this->toDto($result));

            return $this->responseErrorThereIsNoData();
        }, $this->checkAuth);
    }

    /**
     * Update data by id.
     */
    public function update(BaseRequest $request, int $id, bool $restore = false): JsonResponse
    {
        $data = $request->all();

        return $this->execute(function () use ($id, $data, $restore) {
            $result = $this->dbTransaction(fn () => $this->repository->updateById($id, $data, $restore));

            if ($result) return $this->responseWithData($this->toDto($result));

            return $this->responseErrorThereIsNoData();
        }, $this->checkAuth);
    }

    /**
     * Delete item by id.
     */
    public function destroy(BaseRequest $request, int $id): JsonResponse
{
    return $this->execute(function () use ($id) {
        $result = $this->dbTransaction(fn () => $this->repository->deleteById($id));

        if ($result) return $this->responseWithMessage("Deleted successfully");

        return $this->responseErrorThereIsNoData();
    }, $this->checkAuth);
}

}
