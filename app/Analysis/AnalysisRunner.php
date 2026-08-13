<?php

namespace App\Analysis;

use App\Models\AnalysisRule as AnalysisRuleModel;
use App\Models\Dialog;
use Illuminate\Support\Facades\DB;

class AnalysisRunner
{
    public function analyze(Dialog $dialog): void
    {
        $dialog->load('messages');

        DB::transaction(function () use ($dialog) {
            $dialog->events()->delete();

            $rules = AnalysisRuleModel::query()->where('enabled', true)->get();

            foreach ($rules as $ruleModel) {
                $handlerClass = RuleRegistry::resolve($ruleModel->key);

                if ($handlerClass === null) {
                    continue;
                }

                /** @var AnalysisRule $handler */
                $handler = app($handlerClass);
                $detections = $handler->evaluate($dialog, $ruleModel->config ?? []);

                foreach ($detections as $detection) {
                    $dialog->events()->create([
                        'analysis_rule_id' => $ruleModel->id,
                        'severity' => $detection['severity'] ?? $ruleModel->severity,
                        'title' => $detection['title'],
                        'description' => $detection['description'] ?? null,
                        'evidence' => $detection['evidence'] ?? [],
                        'detected_at' => now(),
                    ]);
                }
            }
        });
    }
}
