<script setup lang="ts">
import { FileText, ReceiptText, CheckCircle2, Calendar } from '@lucide/vue';
import type { DashboardDeadline } from '@/types';

defineProps<{ deadlines: DashboardDeadline[] }>();

const deadlineIcon = (type: string) => {
    switch (type) {
        case 'proposal_expiring':
            return FileText;
        case 'invoice_due':
            return ReceiptText;
        case 'task_due':
            return CheckCircle2;
        default:
            return Calendar;
    }
};

const deadlineColor = (type: string) => {
    switch (type) {
        case 'proposal_expiring':
            return 'text-amber-600 bg-amber-50';
        case 'invoice_due':
            return 'text-red-600 bg-red-50';
        case 'task_due':
            return 'text-blue-600 bg-blue-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <div class="rounded-sm border border-border bg-background p-5">
        <h3 class="mb-4 text-lg font-semibold">Upcoming Deadlines</h3>
        <div class="space-y-3">
            <div
                v-for="item in deadlines"
                :key="`${item.type}-${item.title}`"
                class="flex items-start gap-3"
            >
                <div
                    :class="[
                        'flex h-8 w-8 shrink-0 items-center justify-center',
                        deadlineColor(item.type),
                    ]"
                >
                    <component :is="deadlineIcon(item.type)" class="h-4 w-4" />
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">
                        {{ item.title }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ item.date }}
                        <span v-if="item.account"
                            >&middot; {{ item.account }}</span
                        >
                        <span v-if="item.project"
                            >&middot; {{ item.project }}</span
                        >
                    </p>
                </div>
            </div>
            <p v-if="!deadlines.length" class="text-sm text-muted-foreground">
                No upcoming deadlines.
            </p>
        </div>
    </div>
</template>
