<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import proposals from '@/routes/proposals';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { Proposal } from '@/types/models/proposal';



const workspace_id = usePage().props.workspace?.id;
const builderStore = useProposalBuilderStore();
const { proposalTitle, proposalMeta, blocks, templateId, selectedAccountId } =
    storeToRefs(builderStore);
const initialProposal = computed<Proposal>(() => ({
  id: 0,
  workspace_id: workspace_id ?? 0,
  account_id: null,
  created_by: null,
  template_id: null,
  title: 'Untitled proposal',
  proposal_number: null,
  status: 'draft',
  valid_until: null,
  content: [],
  currency: 'USD',
  subtotal: 0,
  discount_total: 0,
  tax_rate: 0,
  tax_amount: 0,
  grand_total: 0,
  requires_deposit: false,
  deposit_type: null,
  deposit_value: null,
  deposit_amount: null,
  token: null,
  password_hash: null,
  signer_name: null,
  signer_email: null,
  signer_company: null,
  signature_data: null,
  signed_ip: null,
  signed_user_agent: null,
  deposit_invoice_id: null,
  project_id: null,
  sent_at: null,
  viewed_at: null,
  last_viewed_at: null,
  view_count: 0,
  decided_at: null,
  expired_at: null,
  decline_reason: null,
  created_at: new Date().toISOString(),
  updated_at: new Date().toISOString(),
}));

const isSaving = ref(false);
const isDirty = ref(false);

const handleSave = async () => {
  if (isSaving.value) return;
  
  isSaving.value = true;
  
  try {
    const numericAccountId = selectedAccountId.value
      ? Number(selectedAccountId.value)
      : null;

    if (!numericAccountId || Number.isNaN(numericAccountId)) {
      throw new Error('Please select an account before saving.');
    }
    
    const proposalData = {
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
  isSaving: computed(() => isSaving.value),
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
      :initial-proposal="initialProposal"
      :on-save="handleSave"
      :is-saving="isSaving"
    />
  </div>
</template>
