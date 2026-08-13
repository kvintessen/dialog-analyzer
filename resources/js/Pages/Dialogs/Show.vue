<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { resultBadgeClass, resultLabel, severityBadgeClass, severityLabel } from '@/lib/severity';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps({
    dialog: { type: Object, required: true },
    messages: { type: Array, required: true },
    events: { type: Array, required: true },
});

const highlightedMessageId = ref(null);

const messagePosition = computed(() => {
    const map = {};
    props.messages.forEach((message, index) => {
        map[message.id] = index + 1;
    });
    return map;
});

function pluralizeEvents(count) {
    const mod10 = count % 10;
    const mod100 = count % 100;

    if (mod10 === 1 && mod100 !== 11) {
        return 'событие';
    }

    if ([2, 3, 4].includes(mod10) && ![12, 13, 14].includes(mod100)) {
        return 'события';
    }

    return 'событий';
}

function formatDateTime(value) {
    return new Date(value).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function goToMessage(messageId) {
    highlightedMessageId.value = messageId;
    const el = document.getElementById(`message-${messageId}`);
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        if (highlightedMessageId.value === messageId) {
            highlightedMessageId.value = null;
        }
    }, 2000);
}

const isAnalyzing = ref(false);

function reanalyze() {
    isAnalyzing.value = true;
    router.post(
        route('dialogs.analyze', props.dialog.id),
        {},
        {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.dialog.analysis_failed) {
                    toast.error('Не удалось выполнить анализ');
                } else {
                    toast.success('Анализ диалога обновлён');
                }
            },
            onError: () => toast.error('Не удалось выполнить анализ'),
            onFinish: () => (isAnalyzing.value = false),
        },
    );
}
</script>

<template>
    <Head :title="`Диалог с ${dialog.client_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Диалог с {{ dialog.client_name }}
                </h2>
                <Badge :class="resultBadgeClass(dialog.result)">
                    {{ resultLabel(dialog.result) }}
                </Badge>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="dialog.analysis_failed"
                    class="mb-6 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive"
                >
                    Последний запуск анализа завершился ошибкой. Показаны результаты предыдущего успешного анализа —
                    попробуйте запустить анализ ещё раз.
                </div>
            </div>

            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div class="lg:col-span-2">
                    <div class="overflow-hidden bg-card text-card-foreground shadow-sm sm:rounded-lg">
                        <div class="flex items-center justify-between border-b border-border p-4">
                            <div class="text-sm text-muted-foreground">
                                Менеджер: <span class="font-medium text-foreground">{{ dialog.manager_name }}</span>
                            </div>
                        </div>
                        <ScrollArea class="h-[32rem] p-4">
                            <div class="space-y-3">
                                <div
                                    v-for="(message, index) in messages"
                                    :id="`message-${message.id}`"
                                    :key="message.id"
                                    class="flex"
                                    :class="message.sender === 'manager' ? 'justify-end' : 'justify-start'"
                                >
                                    <div
                                        class="max-w-[75%] rounded-lg px-3 py-2 shadow-sm transition-colors"
                                        :class="[
                                            message.sender === 'manager'
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-muted text-foreground',
                                            highlightedMessageId === message.id ? 'ring-2 ring-amber-500' : '',
                                        ]"
                                    >
                                        <div class="mb-1 flex items-center justify-between gap-3 text-xs opacity-70">
                                            <span>№{{ index + 1 }} · {{ message.sender === 'manager' ? 'Менеджер' : 'Клиент' }}</span>
                                            <span>{{ formatDateTime(message.sent_at) }}</span>
                                        </div>
                                        <p class="whitespace-pre-wrap text-sm">{{ message.body }}</p>
                                    </div>
                                </div>
                            </div>
                        </ScrollArea>
                    </div>
                </div>

                <div>
                    <div class="overflow-hidden bg-card text-card-foreground shadow-sm sm:rounded-lg">
                        <div class="flex items-center justify-between border-b border-border p-4">
                            <h3 class="font-semibold text-foreground">
                                Обнаружено {{ events.length }} {{ pluralizeEvents(events.length) }}
                            </h3>
                            <Button size="sm" variant="outline" :disabled="isAnalyzing" @click="reanalyze">
                                Повторить анализ
                            </Button>
                        </div>

                        <div class="space-y-3 p-4">
                            <p v-if="events.length === 0" class="text-sm text-muted-foreground">
                                Правила анализа не нашли отклонений в этом диалоге.
                            </p>

                            <div
                                v-for="event in events"
                                :key="event.id"
                                class="rounded-lg border border-border p-3"
                            >
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <Badge :class="severityBadgeClass(event.severity)">
                                        {{ severityLabel(event.severity) }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">{{ event.rule_name }}</span>
                                </div>
                                <p class="text-sm font-medium text-foreground">{{ event.title }}</p>
                                <p v-if="event.description" class="mt-1 text-sm text-muted-foreground">
                                    {{ event.description }}
                                </p>
                                <div
                                    v-if="event.evidence?.message_ids?.length"
                                    class="mt-2 flex flex-wrap gap-1"
                                >
                                    <button
                                        v-for="messageId in event.evidence.message_ids"
                                        :key="messageId"
                                        type="button"
                                        class="rounded border border-border px-2 py-0.5 text-xs text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                                        @click="goToMessage(messageId)"
                                    >
                                        Сообщение №{{ messagePosition[messageId] ?? messageId }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
