<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard } from '@/routes';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import type { BaseBlock } from '@/types/proposal-builder';
import type { Proposal } from '@/types/models/proposal';

const props = defineProps<{
  template_blocks?: BaseBlock[];
}>();

const workspace_id = usePage().props.workspace?.id;
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
  content: props.template_blocks ?? [],
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
    <ProposalBuilder mode="create" :initial-proposal="initialProposal" />
  </div>
</template>
