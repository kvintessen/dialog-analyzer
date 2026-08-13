<?php

namespace App\Analysis;

class RuleRegistry
{
    /**
     * @return array<string, class-string<AnalysisRule>>
     */
    public static function all(): array
    {
        return config('analysis_rules.rules', []);
    }

    /**
     * @return class-string<AnalysisRule>|null
     */
    public static function resolve(string $key): ?string
    {
        $class = static::all()[$key] ?? null;

        if ($class === null) {
            return null;
        }

        if (! is_a($class, AnalysisRule::class, true)) {
            throw new \RuntimeException("Analysis rule [{$key}] resolves to [{$class}], which does not implement ".AnalysisRule::class.'.');
        }

        return $class;
    }
}
