<script setup lang="ts">
import { PenLineIcon, CheckCircleIcon } from '@lucide/vue';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { SignatureModal } from '@/components/ui/signature-modal';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, SignatureBlockData } from '@/types/proposal-builder';

defineProps<{
    data: SignatureBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const builderStore = useProposalBuilderStore();
const showSignatureModal = ref(false);

const portalMode = computed(() => builderStore.portalMode);
const alreadySigned = computed(() => !!builderStore.proposal.signed_at);
const isRejected = computed(() => builderStore.proposal.proposal_status?.automation_trigger === 'declined');
const signerName = computed(() => builderStore.proposal.signer_name || null);
const signerDate = computed(() => {
    const signedAt = builderStore.proposal.signed_at;
    if (!signedAt) return null;
    const date = new Date(signedAt as string);
    return date.toISOString().split('T')[0];
});
const signatureDataUrl = computed(() => {
    const signatureData = builderStore.proposal.signature_data;
    if (!signatureData) return null;
    
    if (typeof signatureData === 'string') {
        try {
            const parsed = JSON.parse(signatureData);
            return parsed.data || null;
        } catch {
            return signatureData;
        }
    }
    
    if (typeof signatureData === 'object' && signatureData.data) {
        return signatureData.data;
    }
    
    return signatureData;
});
</script>

<template>
    <section class="px-8 py-6">
        <div
            v-if="!portalMode && !alreadySigned"
            class="rounded-xl border-2 border-dashed border-border p-8 text-center"
        >
            <div
                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full border border-border bg-muted"
            >
                <PenLineIcon class="h-5 w-5 text-muted-foreground" />
            </div>
            <h3 class="text-sm font-semibold text-foreground">
                {{ data.title || 'Signature' }}
            </h3>
            <p class="mt-1 text-xs text-muted-foreground">
                {{ data.description || 'Client signs here to accept the proposal' }}
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <span
                    v-if="data.require_name"
                    class="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground"
                >
                    Name required
                </span>
                <span
                    v-if="data.require_date"
                    class="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground"
                >
                    Date required
                </span>
                <span
                    v-if="data.require_signature"
                    class="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground"
                >
                    Signature required
                </span>
            </div>
        </div>

        <div
            v-else-if="alreadySigned"
            class="rounded-xl border border-border bg-muted/30 p-6"
        >
            <div
                class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground"
            >
                <CheckCircleIcon class="h-4 w-4 text-green-600" />
                Proposal accepted
            </div>
            <img
                v-if="signatureDataUrl"
                :src="signatureDataUrl"
                alt="Signature"
                class="mb-3 max-h-20 rounded border border-border bg-background p-2"
            />
            <div class="flex gap-4 text-xs text-muted-foreground">
                <div v-if="signerName">
                    <p class="font-medium text-foreground">Signed by</p>
                    <p>{{ signerName }}</p>
                </div>
                <div v-if="signerDate">
                    <p class="font-medium text-foreground">Date</p>
                    <p>{{ signerDate }}</p>
                </div>
            </div>
        </div>

        <div
            v-else-if="isRejected"
            class="rounded-xl border border-red-200 bg-red-50 p-6"
        >
            <div
                class="mb-4 flex items-center gap-2 text-sm font-semibold text-red-700"
            >
                <CheckCircleIcon class="h-4 w-4 text-red-600" />
                Proposal declined
            </div>
            <p class="text-sm text-red-600">
                This proposal has been declined and cannot be signed.
            </p>
            <div v-if="builderStore.proposal.decline_reason" class="mt-2 text-xs text-red-500">
                <p class="font-medium">Reason:</p>
                <p>{{ builderStore.proposal.decline_reason }}</p>
            </div>
        </div>

        <div v-else class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    {{ data.title || 'Sign to accept' }}
                </h3>
                <p
                    v-if="data.description"
                    class="mt-0.5 text-xs text-muted-foreground"
                >
                    {{ data.description }}
                </p>
            </div>

            <Button
                @click="showSignatureModal = true"
                class="w-full"
                size="lg"
            >
                Sign to Accept Proposal
            </Button>
        </div>
    </section>

    <SignatureModal
        :is-open="showSignatureModal"
        :proposal="builderStore.proposal"
        :require-name="data.require_name"
        :require-date="data.require_date"
        :require-signature="data.require_signature"
        :title="data.title || 'Sign Proposal'"
        :description="data.description"
        @close="showSignatureModal = false"
    />
</template>
