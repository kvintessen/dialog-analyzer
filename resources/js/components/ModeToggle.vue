<script setup>
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppColorMode } from '@/composables/useAppColorMode';
import { Moon, Sun } from '@lucide/vue';

const mode = useAppColorMode();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative">
                <Sun class="h-5 w-5 scale-100 dark:scale-0" />
                <Moon class="absolute h-5 w-5 scale-0 dark:scale-100" />
                <span class="sr-only">Переключить тему</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuRadioGroup v-model="mode">
                <DropdownMenuRadioItem value="light">Светлая</DropdownMenuRadioItem>
                <DropdownMenuRadioItem value="dark">Тёмная</DropdownMenuRadioItem>
                <DropdownMenuRadioItem value="auto">Системная</DropdownMenuRadioItem>
            </DropdownMenuRadioGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
