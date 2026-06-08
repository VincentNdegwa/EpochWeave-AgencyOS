<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { dashboard } from '@/routes';
import proposalTemplates from '@/routes/proposal-templates';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import { Button } from '@/components/ui/button';
import { Edit } from '@lucide/vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';
import type { Proposal, ProposalStatus, ProposalTemplate } from '@/types/models/proposal';

const props = defineProps<{
  template: ProposalTemplate;
}>();

const workspace_id = usePage().props.workspace?.id;
const proposalData = computed<Proposal>(() => ({
  id: props.template.id,
  workspace_id: workspace_id ?? 0,
  account_id: null,
  created_by: null,
  template_id: null,
  title: props.template.name,
  proposal_number: null,
  status: 'draft' as ProposalStatus,
  valid_until: null,
  content: props.template.content || [],
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
  created_at: props.template.created_at,
  updated_at: props.template.updated_at,
  mode: 'template' as const, // Distinguish this as a template
}));

const builderStore = useProposalBuilderStore();
const workspaceStore = useWorkspaceStore();

// Initialize builder store with template data
onMounted(() => {
  // Set workspace
  workspaceStore.setWorkspace({
    id: workspace_id,
    name: '',
  });
  
  // Load template content into builder
  builderStore.loadBlocks(props.template.content || []);
  
  // Set template title and meta
  builderStore.setProposalTitle(props.template.name, false);
  builderStore.setProposalMeta({
    currency: 'USD',
    validUntil: null,
    proposalNumber: null,
    depositEnabled: false,
    depositType: 'percentage',
    depositValue: 0,
  }, false);
});

setLayoutProps({
  title: 'view template',
  description: 'Preview your reusable proposal template.',
  breadcrumbs: [
    { title: 'dashboard', href: dashboard() },
    { title: 'templates', href: proposalTemplates.index() },
    { title: props.template.name },
  ],
});
</script>

<template>
  <Head :title="template.name" />
  <div class="-mx-4 -mb-4 h-[calc(100vh-64px)] relative">
    <ProposalCanvas 
      :is-locked="true"
      :builder-mode="'template'"
    />
    
    <div class="fixed bottom-6 right-6 z-50">
      <Link 
        :href="proposalTemplates.edit(template.id)"
        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium bg-primary rounded-lg shadow-lg transition-colors"
      >
        <Edit class="h-4 w-4" />
        Edit Template
      </Link>
    </div>
  </div>
</template>
