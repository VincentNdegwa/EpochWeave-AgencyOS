<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import type { Workspace } from '@/types/models/workspace';

const page = usePage();
const workspace = page.props.workspace as Workspace | null;
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="workspace?.logo_url"
            :src="workspace.logo_url"
            :alt="workspace.display_name!"
            class="size-5 rounded object-cover"
        />
        <AppLogoIcon
            v-else
            class="size-5 fill-current text-white dark:text-black"
        />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ workspace?.display_name || 'Laravel Starter Kit' }}
        </span>
    </div>
</template>
