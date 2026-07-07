<script setup lang="ts">
import { computed } from 'vue';
import { useInitials } from '@/composables/useInitials';
import type { Workspace } from '@/types/models/workspace';

type Props = {
    workspace: Workspace;
    showDescription?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showDescription: false,
});

const { getInitials } = useInitials();

const initials = computed(() => getInitials(props.workspace.display_name || props.workspace.name));
</script>

<template>
    <div class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg bg-primary text-primary-foreground">
        <span class="text-sm font-medium">{{ initials }}</span>
    </div>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ workspace.display_name || workspace.name }}</span>
        <span v-if="showDescription" class="truncate text-xs text-muted-foreground">{{
            workspace.description || 'Workspace'
        }}</span>
    </div>
</template>
