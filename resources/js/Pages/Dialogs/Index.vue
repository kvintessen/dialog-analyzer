<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { resultBadgeClass, resultLabel, severityBadgeClass, severityLabel } from '@/lib/severity';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    dialogs: { type: Array, required: true },
});

function formatDateTime(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Диалоги" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Диалоги
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-card text-card-foreground shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto p-4">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Клиент</TableHead>
                                    <TableHead>Менеджер</TableHead>
                                    <TableHead>Результат</TableHead>
                                    <TableHead>Сообщений</TableHead>
                                    <TableHead>Последнее сообщение</TableHead>
                                    <TableHead>События</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableEmpty v-if="dialogs.length === 0" :colspan="6">
                                    Диалогов пока нет.
                                </TableEmpty>
                                <TableRow
                                    v-for="dialog in dialogs"
                                    :key="dialog.id"
                                    class="cursor-pointer"
                                >
                                    <TableCell class="p-0">
                                        <Link
                                            :href="route('dialogs.show', dialog.id)"
                                            class="block px-4 py-3 font-medium text-foreground"
                                        >
                                            {{ dialog.client_name }}
                                        </Link>
                                    </TableCell>
                                    <TableCell>{{ dialog.manager_name }}</TableCell>
                                    <TableCell>
                                        <Badge :class="resultBadgeClass(dialog.result)">
                                            {{ resultLabel(dialog.result) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ dialog.messages_count }}</TableCell>
                                    <TableCell>{{ formatDateTime(dialog.last_message_at) }}</TableCell>
                                    <TableCell>
                                        <span v-if="dialog.events_count === 0" class="text-muted-foreground">—</span>
                                        <Badge v-else :class="severityBadgeClass(dialog.max_severity)">
                                            {{ dialog.events_count }} · {{ severityLabel(dialog.max_severity) }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
