import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { nanoid } from 'nanoid';
import type { BaseBlock, BlockType } from '@/types/proposal-builder';
import type { ProposalMeta } from '@/types/proposal-meta';
import { createDefaultBlock } from '@/composables/blockFactory';

const defaultProposalMeta = (): ProposalMeta => ({
  currency: 'USD',
  validUntil: null,
  proposalNumber: 'DRAFT',
  depositEnabled: false,
  depositType: 'percentage',
  depositValue: 0,
});

export const useProposalBuilderStore = defineStore('proposalBuilder', () => {
  const blocks = ref<BaseBlock[]>([]);
  const isDirty = ref(false);
  const isSaving = ref(false);
  const selectedBlockId = ref<string | null>(null);
  const lastSavedAt = ref<Date | null>(null);

  const proposalTitle = ref('Untitled proposal');
  const proposalMeta = ref<ProposalMeta>(defaultProposalMeta());

  const orderedBlocks = computed(() =>
    [...blocks.value].sort((a, b) => a.sort_order - b.sort_order)
  );

  const selectedBlock = computed(() =>
    blocks.value.find((b) => b.id === selectedBlockId.value) ?? null
  );

  function loadBlocks(newBlocks: BaseBlock[]) {
    blocks.value = newBlocks;
    isDirty.value = false;
  }

  function setProposalTitle(value: string, markDirty = true) {
    const nextValue = value?.trim() ? value : 'Untitled proposal';
    proposalTitle.value = nextValue;
    if (markDirty) {
      isDirty.value = true;
    }
  }

  function setProposalMeta(meta: ProposalMeta, markDirty = true) {
    proposalMeta.value = { ...defaultProposalMeta(), ...meta };
    if (markDirty) {
      isDirty.value = true;
    }
  }

  function updateProposalMeta(changes: Partial<ProposalMeta>, markDirty = true) {
    proposalMeta.value = { ...proposalMeta.value, ...changes };
    if (markDirty) {
      isDirty.value = true;
    }
  }

  function addBlock(type: BlockType, afterBlockId?: string) {
    const newBlock = createDefaultBlock(type);
    const insertAfterIndex = afterBlockId
      ? blocks.value.findIndex((b) => b.id === afterBlockId)
      : blocks.value.length - 1;

    blocks.value.splice(insertAfterIndex + 1, 0, newBlock);
    reindexBlocks();
    isDirty.value = true;
  }

  function updateBlock(blockId: string, updates: Partial<BaseBlock>) {
    const index = blocks.value.findIndex((b) => b.id === blockId);
    if (index !== -1) {
      blocks.value[index] = { ...blocks.value[index], ...updates };
      isDirty.value = true;
    }
  }

  function updateBlockData(blockId: string, data: any) {
    updateBlock(blockId, { data });
  }

  function updateBlockMeta(blockId: string, meta: any) {
    updateBlock(blockId, { meta });
  }

  function deleteBlock(blockId: string) {
    blocks.value = blocks.value.filter((b) => b.id !== blockId);
    reindexBlocks();
    isDirty.value = true;
  }

  function reorderBlocks(blockIds: string[]) {
    const blockMap = new Map(blocks.value.map((b) => [b.id, b]));
    const reorderedBlocks: BaseBlock[] = [];

    for (const [index, blockId] of blockIds.entries()) {
      const block = blockMap.get(blockId);
      if (block) {
        reorderedBlocks.push({ ...block, sort_order: index });
      }
    }

    blocks.value = reorderedBlocks;
    isDirty.value = true;
  }

  function duplicateBlock(blockId: string) {
    const block = blocks.value.find((b) => b.id === blockId);
    if (block) {
      const newBlock = {
        ...JSON.parse(JSON.stringify(block)),
        id: nanoid(),
        sort_order: block.sort_order + 1,
      };
      blocks.value.splice(block.sort_order + 1, 0, newBlock);
      reindexBlocks();
      isDirty.value = true;
    }
  }

  function selectBlock(blockId: string | null) {
    selectedBlockId.value = blockId;
  }

  function markAsClean() {
    isDirty.value = false;
    lastSavedAt.value = new Date();
  }

  function clear() {
    blocks.value = [];
    isDirty.value = false;
    selectedBlockId.value = null;
    proposalTitle.value = 'Untitled proposal';
    proposalMeta.value = defaultProposalMeta();
  }

  function reindexBlocks() {
    blocks.value = blocks.value.map((block, index) => ({
      ...block,
      sort_order: index,
    }));
  }

  function moveBlock(blockId: string, direction: 'up' | 'down') {
    const currentIndex = blocks.value.findIndex((block) => block.id === blockId);
    if (currentIndex === -1) {
      return;
    }

    const nextIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
    if (nextIndex < 0 || nextIndex >= blocks.value.length) {
      return;
    }

    const updated = [...blocks.value];
    const [removed] = updated.splice(currentIndex, 1);
    updated.splice(nextIndex, 0, removed);
    blocks.value = updated.map((block, index) => ({
      ...block,
      sort_order: index,
    }));
    isDirty.value = true;
  }

  return {
    blocks,
    isDirty,
    isSaving,
    selectedBlockId,
    lastSavedAt,
    proposalTitle,
    proposalMeta,
    orderedBlocks,
    selectedBlock,
    loadBlocks,
    setProposalTitle,
    setProposalMeta,
    updateProposalMeta,
    addBlock,
    updateBlock,
    updateBlockData,
    updateBlockMeta,
    deleteBlock,
    reorderBlocks,
    duplicateBlock,
    selectBlock,
    markAsClean,
    clear,
    reindexBlocks,
    moveBlock,
    setLastSavedAt: (date: Date | null) => {
      lastSavedAt.value = date;
    },
  };
});
