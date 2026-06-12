<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { watch, computed } from 'vue';
import TemplateMetaPanel from '@/pages/proposal-templates/components/TemplateMetaPanel.vue';
import BlockSettingsPanel from '@/pages/proposals/components/sidebar/BlockSettingsPanel.vue';
import ProposalMetaPanel from '@/pages/proposals/components/sidebar/ProposalMetaPanel.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = withDefaults(
    defineProps<{
        mode: 'create' | 'edit';
        builderMode: string;
    }>(),
    {
        mode: 'create',
        builderMode: 'proposal',
    },
);

const store = useProposalBuilderStore();
const { selectedBlockId, sidebarTab } = storeToRefs(store);

const metaTabLabel = computed(() => {
    return props.builderMode === 'template' ? 'Template' : 'Proposal';
});

watch(selectedBlockId, (newValue) => {
    if (newValue) {
        store.setSidebarTab('block');
    }
});
</script>

<template>
    <aside class="flex flex-col bg-background h-full">
        <div class="flex border-b border-border text-sm font-medium">
            <button
                class="flex-1 border-b-2 px-3 py-2 text-center capitalize"
                :class="
                    sidebarTab === 'block'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground'
                "
                type="button"
                @click="store.setSidebarTab('block')"
            >
                Block
            </button>
            <button
                class="flex-1 border-b-2 px-3 py-2 text-center capitalize"
                :class="
                    sidebarTab === 'proposal'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground'
                "
                type="button"
                @click="store.setSidebarTab('proposal')"
            >
                {{ metaTabLabel }}
            </button>
        </div>

        <div class="custom-scrollbar overflow-y-auto min-h-0 flex-1 h-[calc(100vh-100px)]">
            <BlockSettingsPanel v-if="sidebarTab === 'block'" class="h-full" />
            <TemplateMetaPanel
                v-else-if="builderMode === 'template'"
                class="h-full"
            />
            <ProposalMetaPanel v-else class="h-full" />
        </div>
    </aside>
</template>
