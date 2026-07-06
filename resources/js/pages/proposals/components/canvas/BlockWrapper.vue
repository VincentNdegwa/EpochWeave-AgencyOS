<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { computed, provide } from 'vue';
import { blockMetaInjectionKey } from '@/pages/proposals/components/blocks/shared/blockMetaContext';
import BlockLockedOverlay from '@/pages/proposals/components/canvas/BlockLockedOverlay.vue';
import BlockSurface from '@/pages/proposals/components/canvas/BlockSurface.vue';
import BlockToolbar from '@/pages/proposals/components/canvas/BlockToolbar.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, BlockMeta } from '@/types/proposal-builder';
import { defaultBlockMeta } from '@/types/proposal-builder';

const props = defineProps<{
    block: BaseBlock;
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const { selectedBlockId } = storeToRefs(store);
const isSelected = computed(() => selectedBlockId.value === props.block.id);

// The Cover block renders its own full-bleed background and padding, so the
// surrounding surface's padding/background/border styling is skipped for it.
const isFullBleed = computed(() => props.block.type === 'cover');

const meta = computed<BlockMeta>(() => ({
    ...defaultBlockMeta,
    ...props.block.meta,
}));

provide(blockMetaInjectionKey, meta);

const handleSelect = (event: MouseEvent) => {
    if (props.isLocked) {
        return;
    }

    // Only select if the click target is the wrapper itself or its direct children
    // This prevents interfering with nested block clicks
    const target = event.target as Element;
    const wrapper = event.currentTarget as Element;

    // Check if the click is on the wrapper itself or a direct child
    // If it's on a nested element inside the slot, don't handle it
    if (
        target === wrapper ||
        (wrapper.contains(target) && !isNestedElement(target, wrapper))
    ) {
        store.selectBlock(props.block.id);
    }
};

// Helper function to check if an element is nested (not a direct child of wrapper)
const isNestedElement = (target: Element, wrapper: Element): boolean => {
    let current = target.parentElement;

    while (current && current !== wrapper) {
        if (current.hasAttribute('data-nested-block')) {
            return true;
        }

        current = current.parentElement;
    }

    return false;
};
</script>

<template>
    <div
        class="relative"
        :class="[
            isSelected && !props.isLocked
                ? 'ring-2 ring-primary ring-offset-2'
                : 'ring-1 ring-transparent',
        ]"
        @click="handleSelect"
    >
        <BlockToolbar
            v-if="!props.isLocked"
            :block="props.block"
            :is-selected="isSelected"
        />
        <BlockSurface
            :meta="meta"
            :is-locked="props.isLocked"
            :full-bleed="isFullBleed"
        >
            <slot />
        </BlockSurface>
        <BlockLockedOverlay v-if="props.block.is_locked && props.isLocked" />
    </div>
</template>
