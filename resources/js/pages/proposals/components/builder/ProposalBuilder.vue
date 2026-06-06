<script setup lang="ts">
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { watchDebounced } from '@vueuse/core';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import BuilderSidebar from '@/pages/proposals/components/sidebar/BuilderSidebar.vue';
import BuilderTopbar from '@/pages/proposals/components/builder/BuilderTopbar.vue';
import BlockPickerModal from '@/pages/proposals/components/block-picker/BlockPickerModal.vue';
import type { Proposal } from '@/types/models/proposal';
import type { BlockType } from '@/types/proposal-builder';
import type { ProposalMeta } from '@/types/proposal-meta';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

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
const { blocks, isDirty, isSaving } = storeToRefs(builderStore);

const title = ref(props.initialProposal?.title ?? 'Untitled proposal');
const isPreview = ref(false);
const proposalMeta = reactive<ProposalMeta>({
  currency: props.initialProposal?.currency ?? 'USD',
  validUntil: props.initialProposal?.valid_until ?? null,
  proposalNumber: props.initialProposal?.token ?? 'DRAFT',
  depositEnabled: false,
  depositType: 'percentage' as 'percentage' | 'fixed',
  depositValue: 30,
});
const pickerOpen = ref(false);
const insertAfterId = ref<string | null>(null);

watch(
  () => props.initialProposal?.content,
  (incoming) => {
    builderStore.loadBlocks(incoming ?? []);
  },
  { immediate: true, deep: true }
);

watch(
  () => props.initialProposal?.title,
  (newTitle) => {
    if (newTitle) {
      title.value = newTitle;
    }
  }
);

watch(
  () => props.initialProposal?.currency,
  (currency) => {
    if (currency) {
      proposalMeta.currency = currency;
    }
  },
  { immediate: true }
);

watch(
  () => props.initialProposal?.valid_until,
  (validUntil) => {
    proposalMeta.validUntil = validUntil ?? null;
  },
  { immediate: true }
);

const handleAddBlockRequest = (afterBlockId: string | null) => {
  insertAfterId.value = afterBlockId;
  pickerOpen.value = true;
};

const handleBlockPicked = (type: BlockType) => {
  builderStore.addBlock(type, insertAfterId.value ?? undefined);
  pickerOpen.value = false;
  insertAfterId.value = null;
};

watchDebounced(
  () => ({
    blocks: blocks.value,
    title: title.value,
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
});

const isCanvasLocked = computed(() => props.mode === 'edit' && props.initialProposal?.status === 'accepted');

const togglePreview = () => {
  isPreview.value = !isPreview.value;
};
</script>

<template>
  <div class="flex h-full flex-col bg-background">
    <BuilderTopbar
      v-model:title="title"
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
        class="hidden w-80 border-l border-border lg:block"
        v-model:title="title"
        v-model:proposal-meta="proposalMeta"
        :mode="mode"
      />
    </div>
    <BlockPickerModal v-model:open="pickerOpen" @select="handleBlockPicked" />
  </div>
</template>
