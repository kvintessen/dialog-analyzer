<script setup>
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { severityBadgeClass, severityLabel } from '@/lib/severity';

defineProps({
    rules: { type: Array, required: true },
    canEdit: { type: Boolean, required: true },
});

const emit = defineEmits(['edit', 'toggle']);
</script>

<template>
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
                    <div class="font-medium text-foreground">{{ rule.name }}</div>
                    <div class="text-xs text-muted-foreground">{{ rule.description }}</div>
                </TableCell>
                <TableCell>
                    <Badge :class="severityBadgeClass(rule.severity)">
                        {{ severityLabel(rule.severity) }}
                    </Badge>
                </TableCell>
                <TableCell>
                    <Switch
                        :model-value="rule.enabled"
                        :disabled="!canEdit"
                        @update:model-value="(value) => emit('toggle', rule, value)"
                    />
                </TableCell>
                <TableCell class="text-right">
                    <Button
                        v-if="canEdit"
                        size="sm"
                        variant="outline"
                        @click="emit('edit', rule)"
                    >
                        Редактировать
                    </Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
