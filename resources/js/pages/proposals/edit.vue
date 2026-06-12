<script setup lang="ts">
import { Head, router, setLayoutProps } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { dashboard } from '@/routes';
import proposals from '@/routes/proposals';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { Proposal } from '@/types/models/proposal';

const props = defineProps<{
    proposal: Proposal;
}>();

const builderStore = useProposalBuilderStore();
const { proposal: storeProposal } = storeToRefs(builderStore);

watch(
    () => props.proposal,
    (incoming) => {
        builderStore.hydrateProposal(incoming);
    },
    { immediate: true, deep: true },
);

const isSaving = ref(false);

const breadcrumbs = computed(() => [
    { title: 'dashboard', href: dashboard() },
    { title: 'proposals', href: '/proposals' },
    { title: storeProposal.value.title },
]);

const applyLayout = () => {
    setLayoutProps({
        title: 'edit proposal',
        description: 'Update blocks, pricing, and metadata.',
        breadcrumbs: breadcrumbs.value,
    });
};

applyLayout();

watch(
    () => storeProposal.value.title,
    () => applyLayout(),
);

const handleSave = async () => {
    if (isSaving.value) {
        return;
    }

    isSaving.value = true;

    try {
        const numericAccountId = storeProposal.value.account_id ?? null;

        if (!numericAccountId || Number.isNaN(numericAccountId)) {
            throw new Error('Please select an account before saving.');
        }

        // Get all line items from catalog
        const allLineItems = builderStore.getAllLineItems();

        const payload = {
            title: storeProposal.value.title,
            currency: storeProposal.value.currency,
            valid_until: storeProposal.value.valid_until,
            requires_deposit: storeProposal.value.requires_deposit,
            deposit_type: storeProposal.value.deposit_type,
            deposit_value: storeProposal.value.deposit_value,
            template_id: storeProposal.value.template_id,
            account_id: numericAccountId,
            account_contact_id: storeProposal.value.account_contact_id,
            user_id: storeProposal.value.user_id,
            blocks: storeProposal.value.content,
            line_items: allLineItems, // Send all catalog items
        };

        await router.put(proposals.update(props.proposal.id).url, payload, {
            preserveScroll: true,
            onSuccess: () => builderStore.markAsClean(),
        });
    } catch (error) {
        console.error('Failed to update proposal:', error);
    } finally {
        isSaving.value = false;
    }
};
</script>

<template>
    <Head :title="`Edit · ${props.proposal.title}`" />
    <div class="-mx-4 -mb-4 h-[calc(100vh-64px)]">
        <ProposalBuilder
            mode="edit"
            :on-save="handleSave"
            :is-saving="isSaving"
        />
    </div>
</template>
