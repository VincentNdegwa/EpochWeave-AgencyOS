<script setup lang="ts">
import { BarChart3, FileText, ReceiptText, CheckCircle2 } from '@lucide/vue';
import { useCurrency } from '@/composables/useCurrency';
import type { DashboardActivity } from '@/types';

defineProps<{ activities: DashboardActivity[] }>();

const { format: formatCurrency } = useCurrency();

const activityIcon = (type: string) => {
    switch (type) {
        case 'proposal_signed':
            return FileText;
        case 'invoice_sent':
            return ReceiptText;
        case 'task_completed':
            return CheckCircle2;
        default:
            return BarChart3;
    }
};

const activityColor = (type: string) => {
    switch (type) {
        case 'proposal_signed':
            return 'text-emerald-600 bg-emerald-50';
        case 'invoice_sent':
            return 'text-blue-600 bg-blue-50';
        case 'task_completed':
            return 'text-violet-600 bg-violet-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <div class="rounded-sm border border-border bg-background p-5">
        <h3 class="mb-4 text-lg font-semibold">Recent Activity</h3>
        <div class="space-y-3">
            <div
                v-for="item in activities"
                :key="`${item.type}-${item.title}-${item.date}`"
                class="flex items-start gap-3"
            >
                <div
                    :class="[
                        'flex h-8 w-8 shrink-0 items-center justify-center',
                        activityColor(item.type),
                    ]"
                >
                    <component :is="activityIcon(item.type)" class="h-4 w-4" />
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">
                        {{ item.title }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ item.type.replace('_', ' ') }}
                        <span v-if="item.amount"
                            >&middot; {{ formatCurrency(item.amount) }}</span
                        >
                        <span v-if="item.account"
                            >&middot; {{ item.account }}</span
                        >
                        <span v-if="item.project"
                            >&middot; {{ item.project }}</span
                        >
                    </p>
                </div>
            </div>
            <p v-if="!activities.length" class="text-sm text-muted-foreground">
                No recent activity.
            </p>
        </div>
    </div>
</template>
