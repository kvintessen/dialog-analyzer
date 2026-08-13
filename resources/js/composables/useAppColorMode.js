import { useColorMode } from '@vueuse/core';

export function useAppColorMode() {
    return useColorMode({ storageKey: 'color-scheme' });
}
