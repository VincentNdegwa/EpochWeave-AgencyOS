<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { getBlockDefinition } from '@/pages/proposals/components/blocks/registry';
import BlockAddButton from '@/pages/proposals/components/canvas/BlockAddButton.vue';
import BlockWrapper from '@/pages/proposals/components/canvas/BlockWrapper.vue';
import CanvasEmpty from '@/pages/proposals/components/canvas/CanvasEmpty.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const emit = defineEmits<{
    (e: 'add-block', afterBlockId: string | null, blockType: string): void;
}>();

const props = defineProps<{
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const { orderedBlocks } = storeToRefs(store);

const hasBlocks = computed(() => orderedBlocks.value.length > 0);

const handleAddClick = (afterBlockId: string | null, blockType: string) => {
    if (props.isLocked) {
        return;
    }

    emit('add-block', afterBlockId, blockType);
};
</script>

<template>
    <div
        class="custom-scrollbar flex h-full flex-col overflow-y-auto"
    >
        <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-0">
            <div class="rounded-sm border border-border p-6 shadow-sm">
                <div v-if="hasBlocks" class="space-y-0">
                    <template
                        v-for="(block, index) in orderedBlocks"
                        :key="block.id"
                    >
                        <BlockAddButton
                            v-if="!isLocked && index === 0"
                            :after-block-id="null"
                            @request-add="handleAddClick"
                        />
                        <BlockWrapper :block="block" :is-locked="isLocked">
                            <component
                                v-if="getBlockDefinition(block.type)?.component"
                                :is="getBlockDefinition(block.type)?.component"
                                :data="block.data"
                                :block="block"
                                :is-locked="isLocked"
                            />
                            <div
                                v-else
                                class="rounded-lg border border-dashed border-border p-6 text-center text-sm text-muted-foreground"
                            >
                                Block type "{{ block.type }}" is not supported
                                yet.
                            </div>
                        </BlockWrapper>
                        <BlockAddButton
                            v-if="!isLocked"
                            :after-block-id="block.id"
                            @request-add="handleAddClick"
                        />
                    </template>
                </div>
                <CanvasEmpty
                    v-else
                    :is-locked="isLocked"
                    @add-first="
                        (blockType: string) => handleAddClick(null, blockType)
                    "
                />
            </div>
        </div>
    </div>
</template>
