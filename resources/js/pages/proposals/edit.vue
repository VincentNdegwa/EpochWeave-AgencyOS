<script setup lang="ts">
import { Head, router, setLayoutProps } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { dashboard } from '@/routes';
import proposals from '@/routes/proposals';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { Proposal } from '@/types/models/proposal';

const props = defineProps<{
  proposal: Proposal;
}>();

const builderStore = useProposalBuilderStore();
const {
  blocks,
  proposalTitle,
  proposalMeta,
  templateId,
  selectedAccountId,
} = storeToRefs(builderStore);

watch(
  () => props.proposal.account_id,
  (accountId) => {
    builderStore.setSelectedAccountId(accountId ? accountId.toString() : null);
  },
  { immediate: true },
);

watch(
  () => props.proposal.template_id,
  (templateIdValue) => {
    builderStore.setTemplateId(templateIdValue ?? null, false);
  },
  { immediate: true },
);

const isSaving = ref(false);

const breadcrumbs = computed(() => [
  { title: 'dashboard', href: dashboard() },
  { title: 'proposals', href: '/proposals' },
  { title: props.proposal.title },
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
  () => props.proposal.title,
  () => applyLayout(),
);

const handleSave = async () => {
  if (isSaving.value) {
    return;
  }

  isSaving.value = true;

  try {
    const activeAccountId = selectedAccountId.value ?? (props.proposal.account_id ? props.proposal.account_id.toString() : null);
    const numericAccountId = activeAccountId ? Number(activeAccountId) : null;

    if (!numericAccountId || Number.isNaN(numericAccountId)) {
      throw new Error('Please select an account before saving.');
    }

    const payload = {
      title: proposalTitle.value,
      currency: proposalMeta.value.currency,
      valid_until: proposalMeta.value.validUntil,
      requires_deposit: proposalMeta.value.depositEnabled,
      deposit_type: proposalMeta.value.depositType,
      deposit_value: proposalMeta.value.depositValue,
      template_id: templateId.value,
      account_id: numericAccountId,
      blocks: blocks.value,
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
      :initial-proposal="props.proposal"
      :on-save="handleSave"
      :is-saving="isSaving"
    />
  </div>
</template>
