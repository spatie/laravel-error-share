<?php

namespace Spatie\LaravelErrorShare\Actions;

use Illuminate\Foundation\Exceptions\Renderer\Exception;
use Illuminate\Support\Str;
use Throwable;

class ResolveErrorSharePropertiesAction
{
    public function __construct(protected MapLaravelExceptionAction $laravelExceptionAction)
    {
    }

    /**
     * @param Exception $exception
     *
     * @return array{url: string, report: array}|array{error: string}
     */
    public function execute(Exception $exception): array
    {
        try {
            $report = $this->laravelExceptionAction->execute($exception);

            return [
                'url' => Str::of(config('error-share.endpoint'))->rtrim('/')->append('/api/public-reports'),
                'report' => $report,
            ];
        } catch (Throwable $t) {
            return [
                'error' => $t->getMessage()
            ];
        }
    }
}
