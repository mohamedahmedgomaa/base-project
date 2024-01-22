<?php

namespace Gomaa\Test\Base\Services;

use Gomaa\Test\Base\Requests\BaseRequest;
use Illuminate\Http\JsonResponse;

interface ServiceInterface
{

    /**
     * Display a listing of the resource with some filters.
     *
     * @param BaseRequest $request
     * @return JsonResponse
     */
    public function index(BaseRequest $request): JsonResponse;

    /**
     * Show the specified resource by id.
     *
     * @param BaseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(BaseRequest $request,int $id): JsonResponse;

    /**
     * Store a newly created resource in storage.
     *
     * @param BaseRequest $request
     * @return JsonResponse
     */
    public function store(BaseRequest $request): JsonResponse;

    /**
     * Update the specified resource in storage.
     *
     * @param BaseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(BaseRequest $request,int $id): JsonResponse;

    /**
     * Remove the specified resource from storage.
     *
     * @param BaseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(BaseRequest $request,int $id): JsonResponse;

}
