import { computed } from 'vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

/**
 * Composable for block settings components to properly resolve blocks
 * Works for both top-level and nested blocks
 */
export function useBlockSettings<T = any>(blockId: string) {
    const store = useProposalBuilderStore();

    // Use selectedBlock if it matches the blockId, otherwise search recursively
    const block = computed(() => {
        // If the currently selected block matches our blockId, use it (works for nested blocks)
        if (store.selectedBlock?.id === blockId) {
            return store.selectedBlock;
        }

        // Otherwise search recursively (fallback for edge cases)
        return store.findBlockRecursive(blockId) ?? null;
    });

    const data = computed<T | null>(() => (block.value?.data as T) ?? null);

    const updateData = (changes: Partial<T>) => {
        if (!block.value || !data.value) {
            return;
        }

        store.updateBlockDataRecursive(block.value.id, {
            ...data.value,
            ...changes,
        });
    };

    return {
        block,
        data,
        updateData,
    };
}
