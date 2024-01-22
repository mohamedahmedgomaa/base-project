<?php

namespace Gomaa\Test\Base\Utils;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use function auth;


trait AuthUtil
{
    public function user(): ?Authenticatable
    {
        return auth()->user() ?? auth('api')->user() ?? null;
    }

    public function userId(): int
    {
        return $this->user()->id ?? 0;
    }

    public function hasAuth(): bool
    {
        return Auth::guard('api')->check();
    }
}
