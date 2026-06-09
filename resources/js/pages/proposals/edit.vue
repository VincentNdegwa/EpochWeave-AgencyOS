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
const { proposal } = storeToRefs(builderStore);

watch(
  () => props.proposal,
  (incoming) => {
    builderStore.hydrateProposal(incoming);
  },
  { immediate: true, deep: true }
);

const isSaving = ref(false);

const breadcrumbs = computed(() => [
  { title: 'dashboard', href: dashboard() },
  { title: 'proposals', href: '/proposals' },
  { title: proposal.value.title },
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
  () => proposal.value.title,
  () => applyLayout(),
);

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

    const payload = {
      title: proposal.value.title,
      currency: proposal.value.currency,
      valid_until: proposal.value.valid_until,
      requires_deposit: proposal.value.requires_deposit,
      deposit_type: proposal.value.deposit_type,
      deposit_value: proposal.value.deposit_value,
      template_id: proposal.value.template_id,
      account_id: numericAccountId,
      blocks: proposal.value.content,
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
