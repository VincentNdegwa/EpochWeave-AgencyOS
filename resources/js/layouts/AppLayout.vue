<script setup lang="ts">
import ConfirmationDialog from '@/components/ui/confirmation-dialog/ConfirmationDialog.vue';
import { useConfirmation } from '@/composables/useConfirmation';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const { state, handleConfirm, handleCancel } = useConfirmation();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-full p-4">
            <slot />
        </div>
        <ConfirmationDialog
            :open="state.open"
            :title="state.title"
            :description="state.description"
            :confirm-text="state.confirmText"
            :cancel-text="state.cancelText"
            :variant="state.variant"
            @confirm="handleConfirm"
            @cancel="handleCancel"
        />
    </AppLayout>
</template>
