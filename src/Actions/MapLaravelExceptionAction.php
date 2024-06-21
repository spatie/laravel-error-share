<?php

namespace Spatie\LaravelErrorShare\Actions;

use DateTimeImmutable;
use Illuminate\Foundation\Exceptions\Renderer\Exception;

class MapLaravelExceptionAction
{
    public function __construct(
        protected MapLaravelStackTraceAction $mapLaravelStackTraceAction
    ) {}

    public function execute(Exception $exception): array
    {
        return [
            'notifier' => 'laravel-error-share',
            'exception_class' => $exception->class(),
            'seen_at' => (new DateTimeImmutable())->getTimestamp(),
            'application_path' => app_path(),
            'message' => $exception->message(),
            'language' => 'PHP',
            'language_version' => phpversion(),
            'framework_version' => app()->version(),
            'application_version' => null,
            'stage' => app()->environment(),
            'message_level' => null,
            'open_frame_index' => $exception->defaultFrame(),
            'glows' => [],
            'solutions' => [],
            'context' => $this->resolveContext($exception),
            'stacktrace' => $this->mapLaravelStackTraceAction->execute($exception),
            'documentation_links' => [],
            'tracking_uuid' => null,
            'handled' => null,
        ];
    }

    protected function resolveContext(Exception $exception): array
    {
        return [
            'env' => [
                'laravel_version' => app()->version(),
                'laravel_locale' => app()->getLocale(),
                'laravel_config_cached' => app()->configurationIsCached(),
                'app_debug' => config('app.debug'),
                'app_env' => config('app.env'),
                'php_version' => phpversion(),
            ],
            'route' => $this->mapRoute($exception),
            'request' => [
                'url' => $exception->request()->getUri(),
                'ip' => $exception->request()->getClientIp(),
                'method' => $exception->request()->getMethod(),
                'useragent' => $exception->request()->headers->get('User-Agent'),
            ],
            'request_data' => [
                'body' => $exception->request()->all(),
            ],
            'headers' => $exception->requestHeaders(),
            'queries' => $this->mapQueries($exception),
        ];
    }

    protected function mapRoute(Exception $exception): array
    {
        $route = [];

        $routeData = $exception->applicationRouteContext();

        if (array_key_exists('controller', $routeData)) {
            $route['controllerAction'] = $routeData['controller'];
        }

        if (array_key_exists('route name', $routeData)) {
            $route['route'] = $routeData['route name'];
        }

        if (array_key_exists('middleware', $routeData)) {
            $route['middleware'] = $exception->request()->route()->gatherMiddleware();
        }

        if ($routeParams = $exception->applicationRouteParametersContext()) {
            $route['routeParameters'] = json_decode($routeParams, associative: true);
        }

        return $route;
    }

    protected function mapQueries(Exception $exception): array
    {
        $queries = [];

        foreach ($exception->applicationQueries() as $query) {
            $queries[] = [
                'sql' => $query['sql'],
                'time' => $query['time'],
                'connection_name' => $query['connectionName'],
                'bindings' => [],
                'microtime' => null,
            ];
        }

        return $queries;
    }
}
