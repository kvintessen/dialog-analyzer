<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Object, required: true },
    recentDialogs: { type: Array, required: true },
});

const purchasedRate = computed(() => {
    if (props.stats.total_dialogs === 0) {
        return null;
    }

    return Math.round((props.stats.purchased_count / props.stats.total_dialogs) * 100);
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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Диалогов</CardDescription>
                            <CardTitle class="text-3xl">{{ stats.total_dialogs }}</CardTitle>
                        </CardHeader>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Купили</CardDescription>
                            <CardTitle class="text-3xl">
                                {{ stats.purchased_count }}
                                <span v-if="purchasedRate !== null" class="text-base font-normal text-muted-foreground">
                                    ({{ purchasedRate }}%)
                                </span>
                            </CardTitle>
                        </CardHeader>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardDescription>Критичных событий</CardDescription>
                            <CardTitle class="text-3xl" :class="{ 'text-destructive': stats.high_severity_events_count > 0 }">
                                {{ stats.high_severity_events_count }}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                <Card>
                    <CardHeader class="flex-row items-center justify-between space-y-0">
                        <CardTitle>Последние диалоги</CardTitle>
                        <Link :href="route('dialogs.index')" class="text-sm text-muted-foreground hover:text-foreground">
                            Все диалоги →
                        </Link>
                    </CardHeader>
                    <CardContent class="overflow-x-auto p-0">
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
                                <TableEmpty v-if="recentDialogs.length === 0" :colspan="6">
                                    Диалогов пока нет.
                                </TableEmpty>
                                <TableRow
                                    v-for="dialog in recentDialogs"
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
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
