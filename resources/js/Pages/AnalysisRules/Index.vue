<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import RuleEditorDialog from './RuleEditorDialog.vue';
import RulesTable from './RulesTable.vue';

defineProps({
    rules: { type: Array, required: true },
});

const canEdit = computed(() => usePage().props.auth.user.role === 'analyst');

const dialogOpen = ref(false);
const editingRule = ref(null);
const editingKey = ref(0);

function openEditor(rule) {
    editingRule.value = rule;
    editingKey.value += 1;
    dialogOpen.value = true;
}

function toggleEnabled(rule, enabled) {
    router.patch(
        route('analysis-rules.update', rule.id),
        {
            name: rule.name,
            description: rule.description,
            severity: rule.severity,
            enabled,
            config: rule.config,
        },
        {
            preserveScroll: true,
            onSuccess: () => toast.success(enabled ? 'Правило включено' : 'Правило выключено'),
            onError: () => toast.error('Не удалось изменить статус правила'),
        },
    );
}
</script>

<template>
    <Head title="Правила анализа" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Правила анализа
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-card text-card-foreground shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto p-4">
                        <RulesTable
                            :rules="rules"
                            :can-edit="canEdit"
                            @edit="openEditor"
                            @toggle="toggleEnabled"
                        />
                    </div>
                </div>
            </div>
        </div>

        <RuleEditorDialog
            v-if="editingRule"
            :key="editingKey"
            v-model:open="dialogOpen"
            :rule="editingRule"
        />
    </AuthenticatedLayout>
</template>
