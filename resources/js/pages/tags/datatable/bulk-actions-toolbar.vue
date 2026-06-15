<script setup lang="ts">
import { computed } from 'vue';
import type { Tag } from '@/types/models/tag';

const props = defineProps<{
    selectedRows: Tag[];
}>();

const hasSelection = computed(() => props.selectedRows.length > 0);
const selectionText = computed(() => {
    const count = props.selectedRows.length;
    return count === 1 ? '1 item selected' : `${count} items selected`;
});
</script>

<template>
    <div v-if="hasSelection" class="flex items-center justify-between border-b bg-muted/50 p-3">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-muted-foreground">{{ selectionText }}</span>
        </div>
    </div>
</template>
