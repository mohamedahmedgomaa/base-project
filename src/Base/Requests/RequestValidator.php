<?php

namespace Gomaa\Test\Base\Requests;

use Illuminate\Contracts\Validation\Validator;

trait RequestValidator
{
    public static function validateRequest(Validator $validator): array
    {
        $errors = [];
        foreach ($validator->errors()->messages() as $key => $error) {
            $errors[$key] = $error[0];
        }

        return $errors;
    }
}
