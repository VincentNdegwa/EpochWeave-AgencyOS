<script setup lang="ts">
import { ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import BlockSettingsPanel from '@/pages/proposals/components/sidebar/BlockSettingsPanel.vue';
import ProposalMetaPanel from '@/pages/proposals/components/sidebar/ProposalMetaPanel.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { ProposalMeta } from '@/types/proposal-meta';

withDefaults(
  defineProps<{
    mode: 'create' | 'edit';
  }>(),
  {
    mode: 'create',
  }
);

const title = defineModel<string>('title', { default: 'Untitled proposal' });
const proposalMeta = defineModel<ProposalMeta>('proposalMeta', {
  default: () => ({
    currency: 'USD',
    validUntil: null,
    proposalNumber: 'DRAFT',
    depositEnabled: false,
    depositType: 'percentage',
    depositValue: 0,
  }),
});

const activeTab = ref<'block' | 'proposal'>('proposal');
const store = useProposalBuilderStore();
const { selectedBlockId } = storeToRefs(store);

watch(selectedBlockId, (newValue) => {
  if (newValue) {
    activeTab.value = 'block';
  }
});
</script>

<template>
  <aside class="flex h-[calc(100vh-56px)] flex-col bg-background">
    <div class="flex border-b border-border text-sm font-medium">
      <button
        class="flex-1 border-b-2 px-3 py-2 text-center capitalize"
        :class="activeTab === 'block' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground'"
        type="button"
        @click="activeTab = 'block'"
      >
        Block
      </button>
      <button
        class="flex-1 border-b-2 px-3 py-2 text-center capitalize"
        :class="activeTab === 'proposal' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground'"
        type="button"
        @click="activeTab = 'proposal'"
      >
        Proposal
      </button>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar">
      <BlockSettingsPanel v-if="activeTab === 'block'" class="h-full" />
      <ProposalMetaPanel v-else v-model:title="title" v-model:proposal-meta="proposalMeta" class="h-full" />
    </div>
  </aside>
</template>
