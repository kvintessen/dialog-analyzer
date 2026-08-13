<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const props = defineProps({
    status: { type: Number, required: true },
});

const page = usePage();

const messages = {
    403: {
        title: 'Доступ запрещён',
        description: 'У вас нет прав для просмотра этой страницы.',
    },
    404: {
        title: 'Страница не найдена',
        description: 'Проверьте адрес или вернитесь на главную.',
    },
    419: {
        title: 'Истёк срок действия страницы',
        description: 'Обновите страницу и попробуйте ещё раз.',
    },
    500: {
        title: 'Ошибка сервера',
        description: 'Что-то пошло не так на нашей стороне. Мы уже разбираемся.',
    },
    503: {
        title: 'Сервис временно недоступен',
        description: 'Идут технические работы — попробуйте зайти чуть позже.',
    },
};

const message = computed(
    () => messages[props.status] ?? {
        title: 'Что-то пошло не так',
        description: 'Попробуйте повторить действие ещё раз.',
    },
);
</script>

<template>
    <Head :title="message.title" />

    <div class="flex min-h-screen flex-col items-center justify-center gap-6 bg-background px-6 text-center text-foreground">
        <p class="text-sm font-medium text-muted-foreground">Ошибка {{ status }}</p>
        <h1 class="text-3xl font-bold tracking-tight">{{ message.title }}</h1>
        <p class="max-w-md text-muted-foreground">{{ message.description }}</p>

        <Button as-child>
            <Link :href="page.props.auth?.user ? route('dashboard') : route('home')">
                На главную
            </Link>
        </Button>
    </div>
</template>
