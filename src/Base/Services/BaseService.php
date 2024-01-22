<?php

namespace Gomaa\Test\Base\Services;

use Gomaa\Test\Base\Responses\ApiResponse;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/*
 * This class Will do:
 * - Check auth and catch errors in one place
 * - Do transactions to database in one place
 * - validate data
 */
abstract class BaseService
{
    use ApiResponse;


    /**
     * Check if the user has the right permission then complete the task and wrap the code with try/catch
     *
     * @param bool $checkAuth
     * @param callable $callable
     * @return JsonResponse
     */
    public function execute(callable $callable,bool $checkAuth = false): JsonResponse
    {
        try {
            // check auth
            if ($checkAuth) {
                if (!$this->hasAuth())
                    return $this->responseUnauthorized();
            }

            // execute anonymous function
            return $callable();

        } catch (\Exception $e) {
            return $this->responseCatchError($e->getMessage());
        }
    }

    /**
     * Wrap update and delete transaction
     *
     * @param callable $callable
     * @return null
     * @throws Exception
     */
    public function dbTransaction(callable $callable)
    {
        DB::beginTransaction();
        try {
            //execute anonymous function
            $result = $callable() ?? null;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();

        return $result ?? null;
    }

    /**
     * Validate before insert or update data
     *
     * @param array $data
     * @param array $rules
     * @return JsonResponse|null
     */
    public function validate(array $data, array $rules): ?JsonResponse
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails())
            return $this->responseErrorWithValidatorObject($validator->errors());
        return null;
    }
}
