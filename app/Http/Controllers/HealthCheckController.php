<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route as FacadesRoute;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'app'      => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'queue'    => $this->checkQueue(),
            'storage'  => $this->checkStorage(),
            'docs'     => $this->checkDocs(),
        ]);
    }

    private function checkApp(): array
    {
        return [
            'status'      => 'ok',
            'name'        => config('app.name'),
            'environment' => config('app.env'),
            'debug'       => config('app.debug'),
            'php_version' => PHP_VERSION,
            'laravel'     => app()->version(),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status'     => 'ok',
                'driver'     => config('database.default'),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key   = '__healthcheck__';
            $start = microtime(true);
            Cache::put($key, true, 5);
            $hit    = Cache::get($key) === true;
            $latency = round((microtime(true) - $start) * 1000, 2);
            Cache::forget($key);

            return [
                'status'     => $hit ? 'ok' : 'error',
                'driver'     => config('cache.default'),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'driver' => config('cache.default'),
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            $size       = Queue::size();

            return [
                'status'     => 'ok',
                'driver'     => $connection,
                'queue_size' => $size,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'driver' => config('queue.default'),
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $path  = storage_path('framework');
            $writable = is_writable($path);

            return [
                'status'   => $writable ? 'ok' : 'error',
                'writable' => $writable,
                'path'     => $path,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'error'  => $e->getMessage(),
            ];
        }
    }

    private function checkDocs(): array
    {
        $url = url('/api-docs');


        try {
            $start = microtime(true);

            $internalRequest = Request::create('/api-docs', 'GET');

            $response = FacadesRoute::dispatch($internalRequest);

            $latency = round((microtime(true) - $start) * 1000, 2);

            $data = json_decode($response->getContent(), true);


            return [
                'status'     => isset($data['paths']) ? 'ok' : 'error',
                'url'        => $url,
                'http_code'  => 200,
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'url'    => $url,
                'error'  => $e->getMessage(),
            ];
        }
    }
}
