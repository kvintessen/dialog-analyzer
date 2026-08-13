<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const page = usePage();

const features = [
    {
        title: 'Автоматический анализ',
        description:
            'Каждый новый диалог и новое сообщение автоматически ставятся в очередь на анализ — без ручного запуска.',
    },
    {
        title: 'Настраиваемые правила',
        description:
            'Возражения, задержки ответа и другие сигналы качества коммуникации определяются гибкими правилами анализа.',
    },
    {
        title: 'Доступ по менеджерам',
        description:
            'Каждый менеджер видит только свои диалоги — доступ к данным разграничен на уровне ролей.',
    },
];
</script>

<template>
    <Head title="Главная" />

    <div class="min-h-screen bg-background text-foreground">
        <header class="border-b">
            <div
                class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4"
            >
                <span class="text-lg font-semibold">Dialog Analyzer</span>

                <nav class="flex items-center gap-2">
                    <template v-if="page.props.auth.user">
                        <Button as-child>
                            <Link :href="route('dialogs.index')">
                                Мои диалоги
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" as-child>
                            <Link :href="route('login')">Войти</Link>
                        </Button>
                        <Button as-child>
                            <Link :href="route('register')">
                                Регистрация
                            </Link>
                        </Button>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6">
            <section class="flex flex-col items-start gap-6 py-20">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                    Анализ диалогов менеджеров с клиентами
                </h1>
                <p class="max-w-2xl text-lg text-muted-foreground">
                    Сервис разбирает переписки менеджеров с клиентами и
                    подсвечивает возражения, задержки ответа и другие сигналы
                    качества коммуникации — автоматически, по мере поступления
                    сообщений.
                </p>

                <div class="flex gap-3">
                    <template v-if="page.props.auth.user">
                        <Button size="lg" as-child>
                            <Link :href="route('dialogs.index')">
                                Перейти к диалогам
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button size="lg" as-child>
                            <Link :href="route('register')">Начать</Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link :href="route('login')">Войти</Link>
                        </Button>
                    </template>
                </div>
            </section>

            <section class="grid gap-4 pb-20 sm:grid-cols-3">
                <Card v-for="feature in features" :key="feature.title">
                    <CardHeader>
                        <CardTitle>{{ feature.title }}</CardTitle>
                        <CardDescription>
                            {{ feature.description }}
                        </CardDescription>
                    </CardHeader>
                </Card>
            </section>
        </main>
    </div>
</template>
