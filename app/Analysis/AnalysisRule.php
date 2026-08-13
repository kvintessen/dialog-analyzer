<?php

namespace App\Analysis;

use App\Models\Dialog;

interface AnalysisRule
{
    public static function key(): string;

    /**
     * Describes configurable parameters so the UI can render an edit form
     * without knowing anything about this specific rule.
     *
     * @return array<int, array{key: string, label: string, type: string, default: mixed}>
     */
    public static function configSchema(): array;

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array{title: string, description?: ?string, severity?: ?string, evidence: array}>
     */
    public function evaluate(Dialog $dialog, array $config): array;
}
