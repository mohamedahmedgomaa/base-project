<?php

namespace Gomaa\Base\Base\Controllers;

use Gomaa\Base\Base\Services\BaseApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseApiController extends BaseController implements ControllerInterface
{
    public function __construct(BaseApiService $service, array $actions)
    {
        parent::__construct($service, $actions);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $attributes = $request->only($this->service->requestFilters());
        return $this->service->index(resolve($this->actions['index']));
    }

    /**
     * Show the specified resource by id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        return $this->service->show(resolve($this->actions['show']),$id);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        return $this->service->store(resolve($this->actions['store']));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->service->update(resolve($this->actions['update']),$id, $request->get("restore") ?? false);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->service->destroy(resolve($this->actions['destroy']),$id);
    }
}
