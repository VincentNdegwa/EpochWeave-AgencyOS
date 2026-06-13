<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrency } from '@/composables/useCurrency';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import { SignatureModal } from '@/components/ui/signature-modal';
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

const showSignatureModal = ref(false);
const signatureModalMode = ref<'sign' | 'reject'>('sign');

const hasSignatureBlock = computed(() => {
    if (!props.proposal.content) return false;
    return props.proposal.content.some((block: any) => block.type === 'signature');
});

const alreadySigned = computed(() => !!props.proposal.signed_at);
const isRejected = computed(() => props.proposal.proposal_status?.automation_trigger === 'declined');
const showActionButtons = computed(() => !alreadySigned.value && !isRejected.value);

const openSignatureModal = (mode: 'sign' | 'reject') => {
    signatureModalMode.value = mode;
    showSignatureModal.value = true;
};


onMounted(() => {
    workspaceStore.setWorkspace(workspace);
    builderStore.setPortalMode(true);
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
        
        <!-- Floating Action Buttons -->
        <div v-if="showActionButtons" class="fixed bottom-6 right-6 flex flex-col gap-2">
            <Button
                variant="destructive"
                size="lg"
                @click="openSignatureModal('reject')"
                class="shadow-lg"
            >
                Decline
            </Button>
            <Button
                variant="default"
                size="lg"
                @click="openSignatureModal('sign')"
                class="shadow-lg"
            >
                Accept Proposal
            </Button>
        </div>
        
        <!-- Signature Modal -->
        <SignatureModal
            :is-open="showSignatureModal"
            :proposal="props.proposal"
            :require-name="true"
            :require-date="true"
            :require-signature="hasSignatureBlock"
            :mode="signatureModalMode"
            title="Accept Proposal"
            description="Please provide your information to accept this proposal."
            @close="showSignatureModal = false"
        />
    </div>
</template>
