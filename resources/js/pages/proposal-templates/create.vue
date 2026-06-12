<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';
import ProposalTemplateController from '@/actions/App/Http/Controllers/ProposalTemplateController';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { dashboard } from '@/routes';
import proposalTemplates from '@/routes/proposal-templates';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const workspaceId = usePage().props.workspace?.id ?? 0;
const builderStore = useProposalBuilderStore();
const { proposal, templateSettings, builderMode } = storeToRefs(builderStore);

builderStore.resetProposal(workspaceId, { title: 'Untitled Template' });
builderMode.value = 'template';
templateSettings.value = { description: null, thumbnailUrl: null };

const isSaving = ref(false);

const handleSave = async () => {
  if (isSaving.value) {
return;
}
  
  isSaving.value = true;
  
  try {
    const templateData = {
      name: proposal.value.title,
      description: templateSettings.value.description,
      thumbnail_url: templateSettings.value.thumbnailUrl,
      content: proposal.value.content,
    };
    
    await router.post(ProposalTemplateController.store().url, templateData as any);
  } catch (error) {
    console.error('Failed to save template:', error);
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
      :on-save="handleSave"
      :is-saving="isSaving"
    />
  </div>
</template>
