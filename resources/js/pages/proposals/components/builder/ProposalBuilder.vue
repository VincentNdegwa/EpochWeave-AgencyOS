<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import BuilderSidebar from '@/pages/proposals/components/sidebar/BuilderSidebar.vue';
import BuilderTopbar from '@/pages/proposals/components/builder/BuilderTopbar.vue';
import type { Proposal } from '@/types/models/proposal';
import type { BlockType } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';

const props = withDefaults(
  defineProps<{
    mode: 'create' | 'edit';
    initialProposal?: Proposal | null;
  }>(),
  {
    initialProposal: null,
  }
);

const builderStore = useProposalBuilderStore();
const { blocks, isDirty, isSaving, proposalTitle, proposalMeta } = storeToRefs(builderStore);
const workspaceStore = useWorkspaceStore();

const isPreview = ref(false);

watch(
  () => props.initialProposal?.workspace ?? usePage().props.workspace,
  (workspace) => {
    workspaceStore.setWorkspace(workspace ?? null);
  },
  { immediate: true }
);

watch(
  () => props.initialProposal?.content,
  (incoming) => {
    builderStore.loadBlocks(incoming ?? []);
  },
  { immediate: true, deep: true }
);

watch(
  () => props.initialProposal,
  (proposal) => {
    builderStore.setProposalTitle(proposal?.title ?? 'Untitled proposal', false);
    builderStore.setProposalMeta(
      {
        currency: proposal?.currency ?? 'USD',
        validUntil: proposal?.valid_until ?? null,
        proposalNumber: proposal?.token ?? proposal?.proposal_number ?? 'DRAFT',
        depositEnabled: proposal?.requires_deposit ?? false,
        depositType: proposal?.deposit_type ?? 'percentage',
        depositValue: proposal?.deposit_value ?? 0,
      },
      false
    );
  },
  { immediate: true }
);

const handleAddBlockRequest = (afterBlockId: string | null, blockType: string) => {
  builderStore.addBlock(blockType as BlockType, afterBlockId ?? undefined);
};

watchDebounced(
  () => ({
    blocks: blocks.value,
    title: proposalTitle.value,
  }),
  () => {
    if (!isDirty.value) {
      return;
    }

    isSaving.value = true;

    setTimeout(() => {
      builderStore.markAsClean();
      isSaving.value = false;
    }, 800);
  },
  { debounce: 1200, deep: true }
);

onBeforeUnmount(() => {
  builderStore.clear();
  workspaceStore.clear();
});

const isCanvasLocked = computed(() => props.mode === 'edit' && props.initialProposal?.status === 'accepted');

const togglePreview = () => {
  isPreview.value = !isPreview.value;
};
</script>

<template>
  <div class="flex h-full flex-col bg-background">
    <BuilderTopbar
      v-model:title="proposalTitle"
      :mode="mode"
      :status="props.initialProposal?.status ?? 'draft'"
      :is-dirty="isDirty"
      :is-saving="isSaving"
      :is-preview="isPreview"
      :proposal-id="props.initialProposal?.id ?? null"
      @toggle-preview="togglePreview"
    />
    <div class="flex h-[calc(100vh-56px)] flex-1 overflow-hidden">
      <ProposalCanvas class="flex-1" :is-locked="isCanvasLocked || isPreview" @add-block="handleAddBlockRequest" />
      <BuilderSidebar
        v-if="!isPreview"
        class="hidden w-80 border-l border-border lg:flex"
        :mode="mode"
      />
    </div>
  </div>
</template>
