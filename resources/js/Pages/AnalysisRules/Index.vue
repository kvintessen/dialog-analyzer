<script setup>
import { reactive, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { severityBadgeClass, severityLabel } from '@/lib/severity';
import { Head, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

defineProps({
    rules: { type: Array, required: true },
});

const dialogOpen = ref(false);
const editingRule = ref(null);
const configValues = reactive({});

const form = useForm({
    name: '',
    description: '',
    severity: 'medium',
    enabled: true,
    config: {},
});

function openEditor(rule) {
    editingRule.value = rule;
    form.clearErrors();
    form.name = rule.name;
    form.description = rule.description ?? '';
    form.severity = rule.severity;
    form.enabled = rule.enabled;

    Object.keys(configValues).forEach((key) => delete configValues[key]);
    rule.config_schema.forEach((field) => {
        const current = rule.config?.[field.key] ?? field.default;
        configValues[field.key] =
            field.type === 'string_list'
                ? (Array.isArray(current) ? current.join('\n') : '')
                : current;
    });

    dialogOpen.value = true;
}

function submit() {
    const config = {};

    editingRule.value.config_schema.forEach((field) => {
        const value = configValues[field.key];

        if (field.type === 'string_list') {
            config[field.key] = String(value ?? '')
                .split('\n')
                .map((line) => line.trim())
                .filter(Boolean);
        } else if (field.type === 'integer') {
            config[field.key] = Number.parseInt(value, 10) || 0;
        } else {
            config[field.key] = value;
        }
    });

    form.config = config;

    form.patch(route('analysis-rules.update', editingRule.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
            toast.success('Правило обновлено');
        },
        onError: () => toast.error('Проверьте поля формы'),
    });
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Правила анализа
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto p-4">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Название</TableHead>
                                    <TableHead>Критичность</TableHead>
                                    <TableHead>Статус</TableHead>
                                    <TableHead class="text-right">Действия</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="rule in rules" :key="rule.id">
                                    <TableCell>
                                        <div class="font-medium text-gray-900">{{ rule.name }}</div>
                                        <div class="text-xs text-gray-500">{{ rule.description }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="severityBadgeClass(rule.severity)">
                                            {{ severityLabel(rule.severity) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Switch
                                            :model-value="rule.enabled"
                                            @update:model-value="(value) => toggleEnabled(rule, value)"
                                        />
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button size="sm" variant="outline" @click="openEditor(rule)">
                                            Редактировать
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent v-if="editingRule" class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Редактирование правила</DialogTitle>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="space-y-1">
                        <Label for="rule-name">Название</Label>
                        <Input id="rule-name" v-model="form.name" />
                        <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-1">
                        <Label for="rule-description">Описание</Label>
                        <Textarea id="rule-description" v-model="form.description" rows="2" />
                    </div>

                    <div class="space-y-1">
                        <Label>Критичность по умолчанию</Label>
                        <Select v-model="form.severity">
                            <SelectTrigger>
                                <SelectValue placeholder="Критичность" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="low">Низкая</SelectItem>
                                <SelectItem value="medium">Средняя</SelectItem>
                                <SelectItem value="high">Высокая</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <Switch v-model="form.enabled" />
                        <Label>Правило включено</Label>
                    </div>

                    <div
                        v-for="field in editingRule.config_schema"
                        :key="field.key"
                        class="space-y-1"
                    >
                        <Label :for="`config-${field.key}`">{{ field.label }}</Label>
                        <Input
                            v-if="field.type === 'integer'"
                            :id="`config-${field.key}`"
                            v-model="configValues[field.key]"
                            type="number"
                        />
                        <Textarea
                            v-else-if="field.type === 'string_list'"
                            :id="`config-${field.key}`"
                            v-model="configValues[field.key]"
                            rows="4"
                            placeholder="По одному значению на строку"
                        />
                        <Input
                            v-else
                            :id="`config-${field.key}`"
                            v-model="configValues[field.key]"
                        />
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">Сохранить</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
