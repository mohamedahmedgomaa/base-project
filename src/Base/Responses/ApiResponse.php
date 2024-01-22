<?php

namespace Gomaa\Test\Base\Responses;

use Gomaa\Test\Base\Responses\HTTPCode;
use Illuminate\Http\JsonResponse;
use function trans;

trait ApiResponse
{
    use \Gomaa\Test\Base\Responses\Response;

    // Message =========================================================================================================
    public function responseWithMessage(string $message, int $status = HTTPCode::Success): JsonResponse
    {
        return $this->response($message, null, null, $status);
    }

    public function responseSuccessWithMessage(string $message): JsonResponse
    {
        return $this->responseWithMessage($message, HTTPCode::Success);
    }

    public function responseErrorWithMessage(string $message): JsonResponse
    {
        return $this->responseWithMessage($message, HTTPCode::BadRequest);
    }


    // Data =========================================================================================================
    public function responseWithData($data, int $status = HTTPCode::Success): JsonResponse
    {
        return $this->response(null, $data, null, $status);
    }

    public function responseWithItemsAndMeta($items): JsonResponse
    {
        if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $result["status"] = HTTPCode::Success;
            $result["data"] = $items->items();
            $result["meta"] = [
                "count" => $items->count(),
                "total" => $items->total(), //(Not available when using simplePaginate)
                "perPage" => $items->perPage(),
                "currentPage" => $items->currentPage(),
                "lastPage" => $items->lastPage(), // (Not available when using simplePaginate)
                "hasMorePages" => $items->hasMorePages(),
                "firstItem" => $items->firstItem(),
                "lastItem" => $items->lastItem(),
                "url" => $items->url($items->currentPage()),
                "previousPageUrl" => $items->previousPageUrl(),
                "nextPageUrl" => $items->nextPageUrl(),
            ];

            return $this->result($result, HTTPCode::Success);
        }
        return $this->responseWithData($items);
    }

    public function responseWithDataAndList(string $resultKey, $resultValue, array $result = []): JsonResponse
    {
        $result[$resultKey] = $resultValue;
        return $this->responseWithData($result);
    }


    // Errors =========================================================================================================
    public function responseWithError($errors, int $status = HTTPCode::BadRequest): JsonResponse
    {
        return $this->response(null, null, $errors, $status);
    }

    public function responseWithMessageAndError(string $message,array $errors, int $status = HTTPCode::BadRequest): JsonResponse
    {
        return $this->response($message, null, $errors, $status);
    }




    // Utils ===================================================================================================
    // Data Error
    public function responseErrorThereIsNoData(): JsonResponse
    {
        return self::responseErrorWithMessage(trans('There is no data found'));
    }
    public function responseErrorCanNotSaveData(): JsonResponse
    {
        return self::responseErrorWithMessage(trans('Can not save this data'));
    }
    public function responseErrorCanNotDeleteData(): JsonResponse
    {
        return self::responseErrorWithMessage(trans('Can not delete this record'));
    }


    // Access Error
    public function responseUnauthorized(): JsonResponse
    {
        return self::responseWithMessage(trans('Access Denied!'), HTTPCode::Unauthorized);
    }
    public function responseForbidden(): JsonResponse
    {
        return self::responseWithMessage(trans('Access Denied!'), HTTPCode::Forbidden);
    }


    // Catch Error
    public function responseCatchError($catchMessage): JsonResponse
    {
        return self::responseWithMessage($catchMessage, HTTPCode::Exception);
    }



    // Validator Error
    public function responseErrorWithValidatorObject($validatorError): JsonResponse
    {
        return self::responseWithError($validatorError, HTTPCode::ValidatorError);
    }
    public function responseErrorWithValidatorKeyValue($Key, $value): JsonResponse
    {
        return self::responseWithError([$Key => [$value]], HTTPCode::ValidatorError);
    }

}
