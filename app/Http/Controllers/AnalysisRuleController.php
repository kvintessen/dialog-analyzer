<?php

namespace App\Http\Controllers;

use App\Analysis\RuleRegistry;
use App\Http\Requests\UpdateAnalysisRuleRequest;
use App\Models\AnalysisRule;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnalysisRuleController extends Controller
{
    public function index(): Response
    {
        $rules = AnalysisRule::query()
            ->orderBy('name')
            ->get()
            ->map(fn (AnalysisRule $rule) => [
                'id' => $rule->id,
                'key' => $rule->key,
                'name' => $rule->name,
                'description' => $rule->description,
                'severity' => $rule->severity->value,
                'enabled' => $rule->enabled,
                'config' => $rule->config,
                'config_schema' => $this->configSchemaFor($rule->key),
            ]);

        return Inertia::render('AnalysisRules/Index', ['rules' => $rules]);
    }

    public function update(UpdateAnalysisRuleRequest $request, AnalysisRule $analysisRule): RedirectResponse
    {
        $analysisRule->update($request->validated());

        return back()->with('success', 'Правило обновлено.');
    }

    private function configSchemaFor(string $key): array
    {
        $handlerClass = RuleRegistry::resolve($key);

        return $handlerClass ? $handlerClass::configSchema() : [];
    }
}
