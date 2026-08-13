<script setup>
import AppLogo from '@/components/AppLogo.vue';
import ModeToggle from '@/components/ModeToggle.vue';
import { Card } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-background pt-6 sm:justify-center sm:pt-0">
        <div class="flex w-full max-w-sm items-center justify-between px-6 sm:max-w-md sm:px-0">
            <Link href="/">
                <AppLogo class="h-16 w-16 fill-current text-muted-foreground" />
            </Link>

            <ModeToggle />
        </div>

        <Card class="mt-6 w-full sm:max-w-md">
            <slot />
        </Card>
    </div>
</template>
