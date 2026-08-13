<script setup>
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
import { Textarea } from '@/components/ui/textarea';
import { useAnalysisRuleForm } from '@/composables/useAnalysisRuleForm';
import { toast } from 'vue-sonner';

const props = defineProps({
    rule: { type: Object, required: true },
});

const open = defineModel('open', { type: Boolean, required: true });

const { form, configValues, submit } = useAnalysisRuleForm(props.rule);

function handleSubmit() {
    submit({
        onSuccess: () => {
            open.value = false;
            toast.success('Правило обновлено');
        },
        onError: () => toast.error('Проверьте поля формы'),
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Редактирование правила</DialogTitle>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="handleSubmit">
                <div class="space-y-1">
                    <Label for="rule-name">Название</Label>
                    <Input id="rule-name" v-model="form.name" :aria-invalid="!!form.errors.name" />
                    <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-1">
                    <Label for="rule-description">Описание</Label>
                    <Textarea
                        id="rule-description"
                        v-model="form.description"
                        rows="2"
                        :aria-invalid="!!form.errors.description"
                    />
                    <p v-if="form.errors.description" class="text-xs text-destructive">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="space-y-1">
                    <Label>Критичность по умолчанию</Label>
                    <Select v-model="form.severity">
                        <SelectTrigger :aria-invalid="!!form.errors.severity">
                            <SelectValue placeholder="Критичность" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="low">Низкая</SelectItem>
                            <SelectItem value="medium">Средняя</SelectItem>
                            <SelectItem value="high">Высокая</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.severity" class="text-xs text-destructive">
                        {{ form.errors.severity }}
                    </p>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Switch v-model="form.enabled" />
                        <Label>Правило включено</Label>
                    </div>
                    <p v-if="form.errors.enabled" class="text-xs text-destructive">
                        {{ form.errors.enabled }}
                    </p>
                </div>

                <div
                    v-for="field in rule.config_schema"
                    :key="field.key"
                    class="space-y-1"
                >
                    <Label :for="`config-${field.key}`">{{ field.label }}</Label>
                    <Input
                        v-if="field.type === 'integer'"
                        :id="`config-${field.key}`"
                        v-model="configValues[field.key]"
                        type="number"
                        :aria-invalid="!!form.errors[`config.${field.key}`]"
                    />
                    <Textarea
                        v-else-if="field.type === 'string_list'"
                        :id="`config-${field.key}`"
                        v-model="configValues[field.key]"
                        rows="4"
                        placeholder="По одному значению на строку"
                        :aria-invalid="!!form.errors[`config.${field.key}`]"
                    />
                    <Input
                        v-else
                        :id="`config-${field.key}`"
                        v-model="configValues[field.key]"
                        :aria-invalid="!!form.errors[`config.${field.key}`]"
                    />
                    <p v-if="form.errors[`config.${field.key}`]" class="text-xs text-destructive">
                        {{ form.errors[`config.${field.key}`] }}
                    </p>
                </div>

                <DialogFooter>
                    <Button type="submit" :disabled="form.processing">Сохранить</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
