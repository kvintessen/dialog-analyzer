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
        return static::all()[$key] ?? null;
    }
}
