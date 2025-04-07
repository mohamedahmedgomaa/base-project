<?php


namespace Gomaa\Base\Base\Responses;

class HTTPCode{
    const Success = 200;
    const NoContent = 204;
    const BadRequest = 400;
    const Unauthorized = 401;
    const Forbidden = 403;
    const NotFound = 404;
    const Exception = 417;
    const ValidatorError = 422;
    const InternalError = 500;
}
