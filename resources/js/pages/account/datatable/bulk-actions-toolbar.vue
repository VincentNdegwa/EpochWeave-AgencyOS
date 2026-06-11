<script setup lang="ts">
import { Archive, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { Account } from '@/types/models/account';
import { confirm } from '@/composables/useConfirmation';
import { router } from '@inertiajs/vue3';
import accounts from '@/routes/accounts';

interface Props {
    selectedRows: Account[];
}

const props = defineProps<Props>();

const hasSelection = computed(() => props.selectedRows.length > 0);
const selectionText = computed(() => {
    const count = props.selectedRows.length;
    return count === 1 ? '1 item selected' : `${count} items selected`;
});

const handleBulkArchive = async () => {
    if (!hasSelection.value) return;

    const confirmed = await confirm({
        title: props.selectedRows.length === 1 
            ? 'Archive Account' 
            : `Archive ${props.selectedRows.length} Accounts`,
        description: props.selectedRows.length === 1
            ? 'Are you sure you want to archive this account? This can be undone later.'
            : `Are you sure you want to archive ${props.selectedRows.length} accounts? This can be undone later.`,
        confirmText: 'Archive',
        cancelText: 'Cancel',
        variant: 'default',
    });

    if (confirmed) {
        try {
            const accountIds = props.selectedRows.map(account => account.id);
            
            await router.post(accounts.bulkStatus().url, {
                ids: accountIds,
                status: 'archived'
            });
        } catch (error) {
            console.error('Error during bulk archive:', error);
        }
    }
};

const handleBulkDelete = async () => {
    if (!hasSelection.value) return;

    const confirmed = await confirm({
        title: props.selectedRows.length === 1 
            ? 'Delete Account' 
            : `Delete ${props.selectedRows.length} Accounts`,
        description: props.selectedRows.length === 1
            ? 'Are you sure you want to delete this account? This action cannot be undone.'
            : `Are you sure you want to delete ${props.selectedRows.length} accounts? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        try {
            const accountIds = props.selectedRows.map(account => account.id);
            
            await router.post(accounts.bulkDelete().url, {
                ids: accountIds
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
                @click="handleBulkArchive"
                class="gap-1"
            >
                <Archive class="w-4 h-4" />
                Archive
            </Button>
            <Button
                variant="destructive"
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
