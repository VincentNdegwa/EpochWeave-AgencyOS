<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { confirm } from '@/composables/useConfirmation';
import proposals from '@/routes/proposals';
import type { Proposal } from '@/types/models/proposal';

interface Props {
    selectedRows: Proposal[];
}

const props = defineProps<Props>();

const hasSelection = computed(() => props.selectedRows.length > 0);
const selectionText = computed(() => {
    const count = props.selectedRows.length;

    return count === 1 ? '1 item selected' : `${count} items selected`;
});

const handleBulkDelete = async () => {
    if (!hasSelection.value) {
return;
}

    const confirmed = await confirm({
        title: props.selectedRows.length === 1 
            ? 'Delete Proposal' 
            : `Delete ${props.selectedRows.length} Proposals`,
        description: props.selectedRows.length === 1
            ? 'Are you sure you want to delete this proposal? This action cannot be undone.'
            : `Are you sure you want to delete ${props.selectedRows.length} proposals? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        try {
            const proposalIds = props.selectedRows.map(proposal => proposal.id);
            
            await router.post(proposals.bulkDelete().url, {
                ids: proposalIds
            });
        } catch (error) {
            console.error('Error during bulk delete:', error);
        }
    }
};
</script>

<template>
    <div v-if="hasSelection" class="flex items-center justify-between p-3 bg-muted/50 border-b">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-muted-foreground">{{ selectionText }}</span>
        </div>
        
        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                @click="handleBulkDelete"
                class="gap-1"
            >
                <Trash2 class="w-4 h-4" />
                Delete
            </Button>
        </div>
    </div>
</template>
