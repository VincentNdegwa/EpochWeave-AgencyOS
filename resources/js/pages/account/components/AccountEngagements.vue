<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { FileText, FolderOpen, Pencil, Receipt, Trash2 } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    useEngagementDirections,
    useEngagementOutcomes,
    useEngagementStatuses,
    useEngagementTypes,
} from '@/composables/useEnums';
import type { Engagement } from '@/types/models/account';

const props = defineProps<{
    engagements: Engagement[];
}>();

const emit = defineEmits<{
    edit: [engagement: Engagement];
    delete: [engagement: Engagement];
}>();

const formatDate = (value: string | null): string => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString();
};

const { getLabel: typeLabel } = useEngagementTypes();
const { getLabel: directionLabel } = useEngagementDirections();
const { getLabel: statusLabel, getVariant: statusVariant } =
    useEngagementStatuses();
const { getLabel: outcomeLabel, getVariant: outcomeVariant } =
    useEngagementOutcomes();
</script>

<template>
    <div
        v-if="props.engagements.length === 0"
        class="py-8 text-center text-sm text-muted-foreground"
    >
        No engagements yet.
    </div>
    <div v-else class="space-y-3">
        <div
            v-for="engagement in props.engagements"
            :key="engagement.id"
            class="rounded-sm border p-4 text-sm"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <Badge variant="outline" class="text-[11px]">
                        {{ typeLabel(engagement.type) }}
                    </Badge>
                    <Badge variant="outline" class="text-[11px] capitalize">
                        {{ directionLabel(engagement.direction) }}
                    </Badge>
                    <Badge
                        :variant="statusVariant(engagement.status)"
                        class="text-[11px] capitalize"
                    >
                        {{ statusLabel(engagement.status) }}
                    </Badge>
                    <Badge
                        v-if="engagement.outcome"
                        :variant="outcomeVariant(engagement.outcome)"
                        class="text-[11px] capitalize"
                    >
                        {{ outcomeLabel(engagement.outcome) }}
                    </Badge>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs text-muted-foreground">
                        {{ engagement.user?.name ?? 'Unassigned' }}
                    </span>
                    <Button
                        size="sm"
                        variant="ghost"
                        class="h-7 w-7 p-0"
                        @click="emit('edit', engagement)"
                    >
                        <Pencil class="h-3.5 w-3.5" />
                    </Button>
                    <Button
                        size="sm"
                        variant="ghost"
                        class="h-7 w-7 p-0 text-destructive"
                        @click="emit('delete', engagement)"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </Button>
                </div>
            </div>

            <h4 class="mt-2 font-medium text-foreground">
                {{ engagement.subject || 'No subject' }}
            </h4>

            <p
                v-if="engagement.content"
                class="mt-1 line-clamp-2 text-sm text-muted-foreground"
            >
                {{ engagement.content }}
            </p>

            <div
                class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-muted-foreground"
            >
                <span v-if="engagement.scheduled_at">
                    <span class="font-medium">Scheduled:</span>
                    {{ formatDate(engagement.scheduled_at) }}
                </span>
                <span v-if="engagement.completed_at">
                    <span class="font-medium">Completed:</span>
                    {{ formatDate(engagement.completed_at) }}
                </span>
                <span v-if="engagement.follow_up_at">
                    <span class="font-medium">Follow up:</span>
                    {{ formatDate(engagement.follow_up_at) }}
                </span>
            </div>

            <div
                v-if="
                    engagement.proposal ||
                    engagement.invoice ||
                    engagement.project
                "
                class="mt-3 flex flex-wrap gap-3 border-t pt-3"
            >
                <Link
                    v-if="engagement.proposal"
                    :href="`/proposals/${engagement.proposal.id}`"
                    class="inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs transition-colors hover:bg-muted"
                >
                    <FileText class="h-3.5 w-3.5 text-muted-foreground" />
                    {{ engagement.proposal.title }}
                </Link>
                <Link
                    v-if="engagement.invoice"
                    :href="`/invoices/${engagement.invoice.id}`"
                    class="inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs transition-colors hover:bg-muted"
                >
                    <Receipt class="h-3.5 w-3.5 text-muted-foreground" />
                    {{
                        engagement.invoice.invoice_number ??
                        `#${engagement.invoice.id}`
                    }}
                </Link>
                <Link
                    v-if="engagement.project"
                    :href="`/projects/${engagement.project.id}`"
                    class="inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs transition-colors hover:bg-muted"
                >
                    <FolderOpen class="h-3.5 w-3.5 text-muted-foreground" />
                    {{ engagement.project.name }}
                </Link>
            </div>
        </div>
    </div>
</template>
