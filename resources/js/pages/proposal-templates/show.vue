<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import { Edit } from '@lucide/vue';
import { onMounted } from 'vue';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import { dashboard } from '@/routes';
import proposalTemplates from '@/routes/proposal-templates';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';
import type { Proposal, ProposalTemplate } from '@/types/models/proposal';

const props = defineProps<{
  template: ProposalTemplate;
}>();

const workspaceId = usePage().props.workspace?.id ?? 0;
const builderStore = useProposalBuilderStore();
const workspaceStore = useWorkspaceStore();

const toProposal = (template: ProposalTemplate): Proposal => ({
  id: template.id,
  workspace_id: workspaceId,
  account_id: null,
  created_by: null,
  template_id: null,
  title: template.name,
  proposal_number: null,
  status: 'draft',
  valid_until: null,
  content: template.content || [],
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
  created_at: template.created_at,
  updated_at: template.updated_at,
});

onMounted(() => {
  workspaceStore.setWorkspace(usePage().props.workspace ?? null);
  builderStore.hydrateProposal(toProposal(props.template), {
    mode: 'template',
    template: {
      description: props.template.description ?? null,
      thumbnailUrl: props.template.thumbnail_url ?? null,
    },
  });
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
