<?php

namespace Spatie\LaravelErrorShare\Actions;

use Illuminate\Foundation\Exceptions\Renderer\Exception;
use Illuminate\Foundation\Exceptions\Renderer\Frame;

class MapLaravelStackTraceAction
{
    public function __construct(
        protected ?int $maxFrames = null,
    ) {}

    public function execute(Exception $exception): array
    {
        /** @var Frame[] $laravelFrames */
        $laravelFrames = $exception->frames();
        $frames = [];

        for ($i = 0; $i < count($laravelFrames); $i++) {
            if ($this->maxFrames !== null && $i >= $this->maxFrames) {
                break;
            }

            $frames[] = [
                'line_number' => $laravelFrames[$i]->line(),
                'method' => ($laravelFrames[$i + 1] ?? null)?->callable() ?? '/',
                'class' => $laravelFrames[$i]->class(),
                'code_snippet' => $this->mapSnippet($laravelFrames[$i]),
                'file' => $laravelFrames[$i]->file(),
                'is_application_frame' => ! $laravelFrames[$i]->isFromVendor(),
            ];
        }

        return $frames;
    }

    protected function mapSnippet(Frame $frame): array
    {
        $from = max($frame->line() - 5, 1);

        $lines = [];

        $spacesToRemove = PHP_INT_MAX;

        foreach (explode(PHP_EOL, $frame->snippet()) as $i => $line) {
            $lines[$from + $i] = $line;

            preg_match('/^(\s*)/', $line, $matches);
            $initialSpacesCount = strlen($matches[0]);

            if ($initialSpacesCount < $spacesToRemove) {
                $spacesToRemove = $initialSpacesCount;
            }
        }

        if ($spacesToRemove === PHP_INT_MAX) {
            return $lines;
        }

        foreach ($lines as $i => $line) {
            $lines[$i] = substr($line, $spacesToRemove);
        }

        return $lines;
    }
}
