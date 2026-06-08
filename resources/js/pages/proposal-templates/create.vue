<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import proposalTemplates from '@/routes/proposal-templates';
import ProposalTemplateController from '@/actions/App/Http/Controllers/ProposalTemplateController';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { Proposal, ProposalStatus } from '@/types/models/proposal';

const workspace_id = usePage().props.workspace?.id;
const initialProposal = computed<Proposal>(() => ({
  id: 0,
  workspace_id: workspace_id ?? 0,
  account_id: null,
  created_by: null,
  template_id: null,
  title: 'Untitled Template',
  proposal_number: null,
  status: 'draft' as ProposalStatus,
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
  mode: 'template' as const, // Distinguish this as a template
}));

const isSaving = ref(false);
const isDirty = ref(false);

const handleSave = async () => {
  if (isSaving.value) return;
  
  isSaving.value = true;
  
  try {
    // Get the current proposal data from the builder store
    const builderStore = useProposalBuilderStore();
    const { proposalTitle, proposalMeta, blocks } = storeToRefs(builderStore);
    
    const templateData = {
      name: proposalTitle.value,
      description: (proposalMeta.value as any).templateDescription || null,
      thumbnail_url: (proposalMeta.value as any).thumbnailUrl || null,
      content: blocks.value,
    };
    
    await router.post(ProposalTemplateController.store().url, templateData);
  } catch (error) {
    console.error('Failed to save template:', error);
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
  title: 'create template',
  description: 'Launch a new reusable proposal template.',
  breadcrumbs: [
    { title: 'dashboard', href: dashboard() },
    { title: 'templates', href: proposalTemplates.index() },
    { title: 'create' },
  ],
});
</script>

<template>
  <Head title="Create Template" />
  <div class="-mx-4 -mb-4 h-[calc(100vh-64px)]">
    <ProposalBuilder 
      mode="create" 
      :initial-proposal="initialProposal"
      :on-save="handleSave"
      :is-saving="isSaving"
    />
  </div>
</template>
