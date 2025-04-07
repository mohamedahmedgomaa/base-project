<?php

namespace Gomaa\Base\Base\Networks;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Facades\Http as HttpClient;
use Illuminate\Support\Facades\Log;

class NetworkClient
{
    protected $configs = [];
    protected $base_url;
    protected $headers = [];

    public function __construct(string $target, string $type) // ops or marafiq
    {
        $this->setConfigs($target, env('APP_ENV') == 'prod' || env('APP_ENV') == 'predication' ? NetworkTypes::PROD : $type);
        $this->setHeaders();
    }

    public static function stage(): NetworkClient
    {
        return (new NetworkClient(NetworkTypes::OPS,NetworkTypes::STAGE));
    }
    public static function local(): NetworkClient
    {
        return (new NetworkClient(NetworkTypes::OPS,NetworkTypes::LOCAL));
    }

    public function setConfigs($target, $type): void
    {
        $this->configs = config('network')[$target.$type];
        $this->base_url = $this->configs['url'];
    }

    public function setHeaders(): void
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-USER-ID' => auth()->user()->id ?? "",
            'X-USER-FULL-NAME' => auth()->user()->name ?? "",
            'X-USER-ROLE' => 'User',
            'X-USER-CONTACT' => auth()->user()->email ?? ""
        ];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }


    public function parseUrl($endpoint): string
    {
        return $this->base_url . $endpoint;
    }

    private function getAuthToken()
    {
        $response = HttpClient::withOptions(["verify"=>false])->post($this->parseUrl("/oauth/token"), [
            "grant_type" => "client_credentials",
            'client_id' => $this->configs['client_id'],
            'client_secret' => $this->configs['client_secret']
        ]);
//         error_log($this->parseUrl("/oauth/token"));
//         error_log($response->json()['access_token'] ?? " no auth token");
        return $response->json()['access_token'] ?? "";
    }


    public function http(): PendingRequest
    {
        return HttpClient::withHeaders($this->getHeaders())
            ->withOptions(["verify" => false])
            ->withToken($this->getAuthToken());
    }

    public function get(string $endpoint, array $query = [])
    {
        return $this->parseBody($this->http()->get($this->parseUrl($endpoint), $query));
    }

    public function post($endpoint, array $body = [])
    {
       // error_log(json_encode($body));
        return $this->parseBody($this->http()->post($this->parseUrl($endpoint), $body));
    }

    public function patch($endpoint, array $body = [])
    {
        return $this->parseBody($this->http()->patch($this->parseUrl($endpoint), $body));
    }

    public function delete($endpoint)
    {
        return $this->parseBody($this->http()->delete($this->parseUrl($endpoint)));
    }

    /**
     * parse request response
     */
    public function parseBody(HttpResponse $response)
    {
        //error_log(json_encode($response->json()));

        if ($response->successful()) {
//            return $response->object()->data;
            return $response->json();
        } else {
            $res = $response->json();
            $status = $response->status();

            Log::emergency('OLD OPS - API Error', ['code' => $status, 'errors' => $response->throw()]);

            if (isset($res['errors']) && !empty($res['errors'])) {
                $res['status_code'] = $status;
                return $res;
            }

            if ($response->status() == HttpCode::HTTP_NOT_FOUND) {
                $errors = ['Page not found'];
            } elseif ($response->status() == HttpCode::HTTP_INTERNAL_SERVER_ERROR) {
                $errors = isset($res['message']) ? [$res['message']] : ['Internal server error'];
            } elseif ($response->status() == HttpCode::HTTP_ERROR) {
                $errors = isset($res['message']) ? [$res['message']] : ['Bad request'];
            } elseif ($response->status() == HttpCode::HTTP_VALIDATION_ERROR) {
                $errors = isset($res['errors']) ? [$res['errors']] : ['Bad request - check validation or request body'];
            } else {
                return $response->throw();
            }
            return $this->getError($status, $errors);
        }
    }


    public function getError($status, $errors = []): array
    {
        return [
            "status_code" => $status,
            "errors" => $errors,
        ];
    }
}
