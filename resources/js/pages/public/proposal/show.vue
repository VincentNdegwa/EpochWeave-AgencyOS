<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { SignatureModal } from '@/components/ui/signature-modal';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';
import type { Proposal } from '@/types/models/proposal';
import type { Workspace } from '@/types/models/workspace';

const props = defineProps<{
    proposal: Proposal;
    workspace: Workspace;
}>();

const builderStore = useProposalBuilderStore();
const workspaceStore = useWorkspaceStore();
const showSignatureModal = ref(false);
const signatureModalMode = ref<'sign' | 'reject'>('sign');

const hasSignatureBlock = computed(() => {
    if (!props.proposal.content) {
        return false;
    }

    return props.proposal.content.some(
        (block: any) => block.type === 'signature',
    );
});

const alreadySigned = computed(() => !!props.proposal.signed_at);
const isRejected = computed(
    () => props.proposal.proposal_status?.automation_trigger === 'declined',
);
const showActionButtons = computed(
    () => !alreadySigned.value && !isRejected.value,
);

const openSignatureModal = (mode: 'sign' | 'reject') => {
    signatureModalMode.value = mode;
    showSignatureModal.value = true;
};

onMounted(() => {
    workspaceStore.setWorkspace(props.workspace);
    builderStore.setPortalMode(true);
    builderStore.hydrateProposal(props.proposal, { mode: 'proposal' });
});
</script>

<template>
    <div>
        <ProposalCanvas :is-locked="true" :builder-mode="'proposal'" />

        <!-- Floating Action Buttons -->
        <div
            v-if="showActionButtons"
            class="fixed right-6 bottom-6 flex flex-col gap-2"
        >
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
