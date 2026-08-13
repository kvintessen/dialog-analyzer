import { useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

export function useAnalysisRuleForm(rule) {
    const configValues = reactive({});

    rule.config_schema.forEach((field) => {
        const current = rule.config?.[field.key] ?? field.default;
        configValues[field.key] =
            field.type === 'string_list'
                ? (Array.isArray(current) ? current.join('\n') : '')
                : current;
    });

    const form = useForm({
        name: rule.name,
        description: rule.description ?? '',
        severity: rule.severity,
        enabled: rule.enabled,
        config: {},
    });

    function buildConfig() {
        const config = {};

        rule.config_schema.forEach((field) => {
            const value = configValues[field.key];

            if (field.type === 'string_list') {
                config[field.key] = String(value ?? '')
                    .split('\n')
                    .map((line) => line.trim())
                    .filter(Boolean);
            } else if (field.type === 'integer') {
                const parsed = Number.parseInt(value, 10);
                config[field.key] = Number.isNaN(parsed) ? value : parsed;
            } else {
                config[field.key] = value;
            }
        });

        return config;
    }

    function submit({ onSuccess, onError } = {}) {
        form.config = buildConfig();

        form.patch(route('analysis-rules.update', rule.id), {
            preserveScroll: true,
            onSuccess,
            onError,
        });
    }

    return { form, configValues, submit };
}
