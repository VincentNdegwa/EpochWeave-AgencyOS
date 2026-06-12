<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Power, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { confirm } from '@/composables/useConfirmation';
import products from '@/routes/products';
import type { Product } from '@/types/models/product';

interface Props {
    selectedRows: Product[];
}

const props = defineProps<Props>();

const hasSelection = computed(() => props.selectedRows.length > 0);
const selectionText = computed(() => {
    const count = props.selectedRows.length;

    return count === 1 ? '1 item selected' : `${count} items selected`;
});

const handleBulkDeactivate = async () => {
    if (!hasSelection.value) {
return;
}

    const confirmed = await confirm({
        title: props.selectedRows.length === 1 
            ? 'Deactivate Product' 
            : `Deactivate ${props.selectedRows.length} Products`,
        description: props.selectedRows.length === 1
            ? 'Are you sure you want to deactivate this product? This can be undone later.'
            : `Are you sure you want to deactivate ${props.selectedRows.length} products? This can be undone later.`,
        confirmText: 'Deactivate',
        cancelText: 'Cancel',
        variant: 'default',
    });

    if (confirmed) {
        try {
            const productIds = props.selectedRows.map(product => product.id);
            
            await router.post(products.bulkStatus().url, {
                ids: productIds,
                is_active: false
            });
        } catch (error) {
            console.error('Error during bulk deactivate:', error);
        }
    }
};

const handleBulkActivate = async () => {
    if (!hasSelection.value) {
return;
}

    const confirmed = await confirm({
        title: props.selectedRows.length === 1 
            ? 'Activate Product' 
            : `Activate ${props.selectedRows.length} Products`,
        description: props.selectedRows.length === 1
            ? 'Are you sure you want to activate this product?'
            : `Are you sure you want to activate ${props.selectedRows.length} products?`,
        confirmText: 'Activate',
        cancelText: 'Cancel',
        variant: 'default',
    });

    if (confirmed) {
        try {
            const productIds = props.selectedRows.map(product => product.id);
            
            await router.post(products.bulkStatus().url, {
                ids: productIds,
                is_active: true
            });
        } catch (error) {
            console.error('Error during bulk activate:', error);
        }
    }
};

const handleBulkDelete = async () => {
    if (!hasSelection.value) {
return;
}

    const confirmed = await confirm({
        title: props.selectedRows.length === 1 
            ? 'Delete Product' 
            : `Delete ${props.selectedRows.length} Products`,
        description: props.selectedRows.length === 1
            ? 'Are you sure you want to delete this product? This action cannot be undone.'
            : `Are you sure you want to delete ${props.selectedRows.length} products? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'destructive',
    });

    if (confirmed) {
        try {
            const productIds = props.selectedRows.map(product => product.id);
            
            await router.post(products.bulkDelete().url, {
                ids: productIds
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
                @click="handleBulkActivate"
                class="gap-1"
            >
                <Power class="w-4 h-4" />
                Activate
            </Button>
            <Button
                variant="outline"
                size="sm"
                @click="handleBulkDeactivate"
                class="gap-1"
            >
                <Power class="w-4 h-4" />
                Deactivate
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
