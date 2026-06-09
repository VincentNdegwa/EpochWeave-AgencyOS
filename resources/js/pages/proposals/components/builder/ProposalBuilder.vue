<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import BuilderSidebar from '@/pages/proposals/components/sidebar/BuilderSidebar.vue';
import BuilderTopbar from '@/pages/proposals/components/builder/BuilderTopbar.vue';
import type { BlockType } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';

const props = withDefaults(
  defineProps<{
    mode: 'create' | 'edit';
    onSave?: () => Promise<void>;
    isSaving?: boolean;
  }>(),
  {
    onSave: undefined,
    isSaving: false,
  }
);

const builderStore = useProposalBuilderStore();
const { proposal, isDirty, isSaving, builderMode } = storeToRefs(builderStore);
const workspaceStore = useWorkspaceStore();

const workspace = usePage().props.workspace;
workspaceStore.setWorkspace(workspace ?? null);

const isPreview = ref(false);

const proposalTitleModel = computed({
  get: () => proposal.value.title,
  set: (value: string) => {
    proposal.value.title = value?.trim() ? value : 'Untitled proposal';
  },
});

const handleAddBlockRequest = (afterBlockId: string | null, blockType: string) => {
  builderStore.addBlock(blockType as BlockType, afterBlockId ?? undefined);
};

watchDebounced(
  () => ({
    blocks: proposal.value.content,
    title: proposal.value.title,
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

const isCanvasLocked = computed(() => props.mode === 'edit' && proposal.value.status === 'accepted');

const togglePreview = () => {
  isPreview.value = !isPreview.value;
};

const proposalStatus = computed(() => proposal.value.status ?? 'draft');
const proposalId = computed(() => proposal.value.id ?? null);
</script>

<template>
  <div class="flex h-full flex-col bg-background">
    <BuilderTopbar
      v-model:title="proposalTitleModel"
      :mode="mode"
      :status="proposalStatus"
      :is-dirty="isDirty"
      :is-saving="props.isSaving || isSaving"
      :is-preview="isPreview"
      :proposal-id="proposalId"
      :builder-mode="builderMode"
      :on-save="props.onSave"
      @toggle-preview="togglePreview"
    />
    <div class="flex h-[calc(100vh-56px)] flex-1 overflow-hidden">
      <ProposalCanvas 
        class="flex-1" 
        :is-locked="isCanvasLocked || isPreview" 
        :builder-mode="builderMode"
        @add-block="handleAddBlockRequest" 
      />
      <BuilderSidebar
        v-if="!isPreview"
        class="hidden w-80 border-l border-border lg:flex"
        :mode="mode"
        :builder-mode="builderMode"
      />
    </div>
  </div>
</template>
