import type { ComputedRef } from 'vue';
import { inject } from 'vue';
import type { BlockMeta } from '@/types/proposal-builder';

export const blockMetaInjectionKey = Symbol('block-meta');

export function useBlockMeta(): ComputedRef<BlockMeta> {
    const meta = inject<ComputedRef<BlockMeta>>(blockMetaInjectionKey);

    if (!meta) {
        throw new Error(
            'useBlockMeta must be used within a BlockWrapper context.',
        );
    }

    return meta;
}
