<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrency } from '@/composables/useCurrency';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';
import { usePage } from '@inertiajs/vue3';
import type { Proposal } from '@/types/models/proposal';

const props = defineProps<{
    proposal: Proposal;
    workspace: any;
}>();

const builderStore = useProposalBuilderStore();
const workspaceStore = useWorkspaceStore();
const page = usePage();
const workspace = page.props.workspace ?? null;

onMounted(() => {
    workspaceStore.setWorkspace(workspace);
    builderStore.hydrateProposal(props.proposal, { mode: 'proposal' });
});

const { format: formatCurrency } = useCurrency();

const formattedValue = computed(() =>
    formatCurrency(props.proposal.grand_total ?? 0, {
        currency: props.proposal.currency ?? 'USD',
    }),
);

const depositSummary = computed(() => {
    if (!props.proposal.requires_deposit) {
        return 'Not required';
    }

    if (props.proposal.deposit_type === 'percentage') {
        return `${props.proposal.deposit_value ?? 0}% upfront`;
    }

    return formatCurrency(
        props.proposal.deposit_amount ?? props.proposal.deposit_value ?? 0,
        {
            currency: props.proposal.currency ?? 'USD',
        },
    );
});

const proposalNumber = computed(
    () => props.proposal.proposal_number ?? props.proposal.token ?? 'DRAFT',
);

const validUntilDisplay = computed(() =>
    formatDate(props.proposal.valid_until),
);
const validityStatus = computed(() =>
    describeValidity(props.proposal.valid_until),
);

function formatDate(value?: string | null): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(
        new Date(value),
    );
}

function formatDateTime(value?: string | null): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function describeValidity(value?: string | null): string {
    if (!value) {
        return 'No expiry date';
    }

    const target = new Date(value).getTime();
    const diffDays = Math.ceil((target - Date.now()) / 86_400_000);

    if (diffDays < 0) {
        return 'Expired';
    }

    if (diffDays === 0) {
        return 'Expires today';
    }

    if (diffDays === 1) {
        return 'Expires tomorrow';
    }

    return `Expires in ${diffDays} days`;
}
</script>

<template>
    <div class="flex h-[calc(100vh-64px)] flex-col">
        <div class="flex-1 overflow-hidden">
            <ProposalCanvas :is-locked="true" :builder-mode="'proposal'" />
        </div>
    </div>
</template>
