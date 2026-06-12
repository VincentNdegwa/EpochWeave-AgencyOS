<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { dashboard } from '@/routes';
import proposals from '@/routes/proposals';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const workspaceId = usePage().props.workspace?.id ?? 0;
const builderStore = useProposalBuilderStore();
builderStore.resetProposal(workspaceId);

const { proposal } = storeToRefs(builderStore);

const isSaving = ref(false);

const handleSave = async () => {
    if (isSaving.value) {
        return;
    }

    isSaving.value = true;

    try {
        const numericAccountId = proposal.value.account_id ?? null;

        if (!numericAccountId || Number.isNaN(numericAccountId)) {
            throw new Error('Please select an account before saving.');
        }

        // Get all line items from catalog
        const allLineItems = builderStore.getAllLineItems();

        const proposalData = {
            title: proposal.value.title,
            currency: proposal.value.currency,
            valid_until: proposal.value.valid_until,
            requires_deposit: proposal.value.requires_deposit,
            deposit_type: proposal.value.deposit_type,
            deposit_value: proposal.value.deposit_value,
            template_id: proposal.value.template_id,
            account_id: numericAccountId,
            blocks: proposal.value.content,
            line_items: allLineItems, // Send all catalog items
        };

        await router.post(proposals.store().url, proposalData);
    } catch (error) {
        console.error('Failed to save proposal:', error);
    } finally {
        isSaving.value = false;
    }
};

// Provide save functionality to child components
defineExpose({
    handleSave,
    isSaving,
});

setLayoutProps({
    title: 'create proposal',
    description: 'Launch a new interactive proposal.',
    breadcrumbs: [
        { title: 'dashboard', href: dashboard() },
        { title: 'proposals', href: '/proposals' },
        { title: 'create' },
    ],
});
</script>

<template>
    <Head title="Create proposal" />
    <div class="-mx-4 -mb-4 h-[calc(100vh-64px)]">
        <ProposalBuilder
            mode="create"
            :on-save="handleSave"
            :is-saving="isSaving"
        />
    </div>
</template>
