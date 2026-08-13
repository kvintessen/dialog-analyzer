<script setup>
import AppLogo from '@/components/AppLogo.vue';
import ModeToggle from '@/components/ModeToggle.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Toaster } from '@/components/ui/sonner';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';
import { ChevronDown, Menu, X } from '@lucide/vue';
import { ref } from 'vue';

const showingNavigationDropdown = ref(false);

const navLinks = [
    { href: () => route('dashboard'), active: () => route().current('dashboard'), label: 'Dashboard' },
    { href: () => route('dialogs.index'), active: () => route().current('dialogs.*'), label: 'Диалоги' },
    { href: () => route('analysis-rules.index'), active: () => route().current('analysis-rules.*'), label: 'Правила анализа' },
];
</script>

<template>
    <div>
        <div class="min-h-screen bg-background">
            <nav class="border-b border-border bg-background">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <AppLogo class="block h-9 w-auto fill-current text-foreground" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <Link
                                    v-for="link in navLinks"
                                    :key="link.label"
                                    :href="link.href()"
                                    :class="
                                        cn(
                                            'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors',
                                            link.active()
                                                ? 'border-primary text-foreground'
                                                : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground',
                                        )
                                    "
                                >
                                    {{ link.label }}
                                </Link>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-2">
                            <ModeToggle />

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" size="sm" class="gap-1">
                                        {{ $page.props.auth.user.name }}
                                        <ChevronDown class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('profile.edit')">Profile</Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('logout')" method="post" as="button" class="w-full text-left">
                                            Log Out
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="-me-2 flex items-center gap-1 sm:hidden">
                            <ModeToggle />

                            <Button
                                variant="ghost"
                                size="icon"
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                            >
                                <Menu v-if="!showingNavigationDropdown" class="h-6 w-6" />
                                <X v-else class="h-6 w-6" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div :class="cn('sm:hidden', showingNavigationDropdown ? 'block' : 'hidden')">
                    <div class="space-y-1 pb-3 pt-2">
                        <Link
                            v-for="link in navLinks"
                            :key="link.label"
                            :href="link.href()"
                            :class="
                                cn(
                                    'block border-l-4 py-2 ps-3 pe-4 text-base font-medium transition-colors',
                                    link.active()
                                        ? 'border-primary bg-accent text-foreground'
                                        : 'border-transparent text-muted-foreground hover:border-border hover:bg-accent hover:text-foreground',
                                )
                            "
                        >
                            {{ link.label }}
                        </Link>
                    </div>

                    <div class="border-t border-border pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-foreground">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-muted-foreground">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <Link
                                :href="route('profile.edit')"
                                class="block px-4 py-2 text-base font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                            >
                                Profile
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full px-4 py-2 text-start text-base font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="border-b border-border bg-background">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>

        <Toaster position="bottom-right" rich-colors />
    </div>
</template>
