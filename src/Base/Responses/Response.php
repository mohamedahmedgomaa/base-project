<?php

namespace Gomaa\Base\Base\Responses;

use Gomaa\Base\Base\Responses\HTTPCode;
use Illuminate\Http\JsonResponse;
use function response;


trait Response
{
    /**
     * Data is returned to the application from here only.
     *
     * @param array $result
     * @param int $responseStatus
     * @return JsonResponse
     */
    private function result(array $result = [], int $responseStatus = HTTPCode::Success): JsonResponse
    {
        return response()->json(
            $result,
            $responseStatus,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8'
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Set JSON Response
     *
     * @param string|null $message
     * @param  $data
     * @param  $errors
     * @param int $status
     *
     * @return JsonResponse
     */
    private function response(string $message = null, $data = null, $errors = null, int $status = HTTPCode::Success): JsonResponse
    {
        $result = ["status" => $status];

        if ($message) $result["message"] = $message;
        if ($data) $result["data"] = $data;
        if ($errors) $result["errors"] = $errors;

        return $this->result($result, $status);
    }
}
