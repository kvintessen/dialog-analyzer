<?php

namespace App\Http\Requests;

use App\Analysis\RuleRegistry;
use App\Enums\EventSeverity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnalysisRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', Rule::enum(EventSeverity::class)],
            'enabled' => ['required', 'boolean'],
            'config' => ['nullable', 'array'],
            ...$this->configFieldRules(),
        ];
    }

    /**
     * Validate each config field against the schema declared by the rule
     * handler class, so malformed config can't reach AnalysisRunner.
     *
     * @return array<string, array<mixed>>
     */
    private function configFieldRules(): array
    {
        $analysisRule = $this->route('analysisRule');
        $handlerClass = $analysisRule ? RuleRegistry::resolve($analysisRule->key) : null;

        if ($handlerClass === null) {
            return [];
        }

        $rules = [];

        foreach ($handlerClass::configSchema() as $field) {
            $key = "config.{$field['key']}";

            $rules[$key] = match ($field['type']) {
                'integer' => ['required', 'integer'],
                'string_list' => ['required', 'array'],
                default => ['required', 'string'],
            };

            if ($field['type'] === 'string_list') {
                $rules["{$key}.*"] = ['string'];
            }
        }

        return $rules;
    }
}
