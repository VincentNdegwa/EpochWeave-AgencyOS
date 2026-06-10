import { defineStore } from 'pinia';
import { computed, nextTick, ref, watch } from 'vue';
import { nanoid } from 'nanoid';
import type { BaseBlock, BlockType } from '@/types/proposal-builder';
import type { Proposal } from '@/types/models/proposal';
import { createDefaultBlock } from '@/composables/blockFactory';

type BuilderMode = 'proposal' | 'template';

export interface TemplateSettings {
  description: string | null;
  thumbnailUrl: string | null;
}

const cloneProposal = (payload: Proposal): Proposal => {
  return JSON.parse(JSON.stringify(payload)) as Proposal;
};

const createDefaultProposal = (workspaceId = 0): Proposal => {
  const timestamp = new Date().toISOString();

  return {
    id: 0,
    workspace_id: workspaceId,
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
    created_at: timestamp,
    updated_at: timestamp,
  };
};

export const useProposalBuilderStore = defineStore('proposalBuilder', () => {
  const isDirty = ref(false);
  const isSaving = ref(false);
  const selectedBlockId = ref<string | null>(null);
  const sidebarTab = ref<'block' | 'proposal'>('proposal');
  const lastSavedAt = ref<Date | null>(null);
  const builderMode = ref<BuilderMode>('proposal');
  const hydrating = ref(false);

  const proposal = ref<Proposal>(createDefaultProposal());
  const templateSettings = ref<TemplateSettings>({ description: null, thumbnailUrl: null });

  const orderedBlocks = computed(() =>
    [...proposal.value.content].sort((a, b) => a.sort_order - b.sort_order)
  );

  const selectedBlock = computed(() =>
    proposal.value.content.find((b) => b.id === selectedBlockId.value) ?? null
  );

  watch(
    proposal,
    () => {
      if (hydrating.value) {
        return;
      }
      isDirty.value = true;
    },
    { deep: true }
  );

  function loadBlocks(newBlocks: BaseBlock[]) {
    proposal.value.content = [...newBlocks];
    isDirty.value = false;
  }

  function addBlock(type: BlockType, afterBlockId?: string) {
    const newBlock = createDefaultBlock(type);
    const insertAfterIndex = afterBlockId
      ? proposal.value.content.findIndex((b) => b.id === afterBlockId)
      : proposal.value.content.length - 1;

    proposal.value.content.splice(insertAfterIndex + 1, 0, newBlock);
    reindexBlocks();
    selectedBlockId.value = newBlock.id;
    sidebarTab.value = 'block';
    isDirty.value = true;
  }

  function updateBlock(blockId: string, updates: Partial<BaseBlock>) {
    const index = proposal.value.content.findIndex((b) => b.id === blockId);
    if (index !== -1) {
      proposal.value.content[index] = { ...proposal.value.content[index], ...updates };
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
    proposal.value.content = proposal.value.content.filter((b) => b.id !== blockId);
    reindexBlocks();
    isDirty.value = true;
  }

  function reorderBlocks(blockIds: string[]) {
    const blockMap = new Map(proposal.value.content.map((b) => [b.id, b]));
    const reorderedBlocks: BaseBlock[] = [];

    for (const [index, blockId] of blockIds.entries()) {
      const block = blockMap.get(blockId);
      if (block) {
        reorderedBlocks.push({ ...block, sort_order: index });
      }
    }

    proposal.value.content = reorderedBlocks;
    isDirty.value = true;
  }

  function duplicateBlock(blockId: string) {
    const block = proposal.value.content.find((b) => b.id === blockId);
    if (block) {
      const newBlock = {
        ...JSON.parse(JSON.stringify(block)),
        id: nanoid(),
        sort_order: block.sort_order + 1,
      };
      proposal.value.content.splice(block.sort_order + 1, 0, newBlock);
      reindexBlocks();
      isDirty.value = true;
    }
  }

  function setSidebarTab(tab: 'block' | 'proposal') {
    sidebarTab.value = tab;
  }

  function selectBlock(blockId: string | null) {
    selectedBlockId.value = blockId;
  }

  function markAsClean() {
    isDirty.value = false;
    lastSavedAt.value = new Date();
  }

  function resetProposal(workspaceId = 0, overrides: Partial<Proposal> = {}) {
    hydrating.value = true;
    proposal.value = cloneProposal({
      ...createDefaultProposal(workspaceId),
      ...overrides,
    });
    nextTick(() => {
      hydrating.value = false;
      markAsClean();
    });
  }

  function hydrateProposal(payload: Proposal, options?: { mode?: BuilderMode; template?: TemplateSettings }) {
    hydrating.value = true;
    proposal.value = cloneProposal(payload);
    if (options?.mode) {
      builderMode.value = options.mode;
    }
    if (options?.template) {
      templateSettings.value = { ...options.template };
    }
    nextTick(() => {
      hydrating.value = false;
      markAsClean();
    });
  }

  function clear() {
    resetProposal();
    selectedBlockId.value = null;
    sidebarTab.value = 'proposal';
    builderMode.value = 'proposal';
    templateSettings.value = { description: null, thumbnailUrl: null };
  }

  function reindexBlocks() {
    proposal.value.content = proposal.value.content.map((block, index) => ({
      ...block,
      sort_order: index,
    }));
  }

  function moveBlock(blockId: string, direction: 'up' | 'down') {
    const currentIndex = proposal.value.content.findIndex((block) => block.id === blockId);
    if (currentIndex === -1) {
      return;
    }

    const nextIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
    if (nextIndex < 0 || nextIndex >= proposal.value.content.length) {
      return;
    }

    const updated = [...proposal.value.content];
    const [removed] = updated.splice(currentIndex, 1);
    updated.splice(nextIndex, 0, removed);
    proposal.value.content = updated.map((block, index) => ({
      ...block,
      sort_order: index,
    }));
    isDirty.value = true;
  }

  const blocks = computed(() => proposal.value.content);

  return {
    proposal,
    blocks,
    isDirty,
    isSaving,
    selectedBlockId,
    sidebarTab,
    lastSavedAt,
    builderMode,
    templateSettings,
    orderedBlocks,
    selectedBlock,
    loadBlocks,
    addBlock,
    updateBlock,
    updateBlockData,
    updateBlockMeta,
    deleteBlock,
    reorderBlocks,
    duplicateBlock,
    selectBlock,
    setSidebarTab,
    markAsClean,
    resetProposal,
    hydrateProposal,
    clear,
    reindexBlocks,
    moveBlock,
    setLastSavedAt: (date: Date | null) => {
      lastSavedAt.value = date;
    },
  };
});

