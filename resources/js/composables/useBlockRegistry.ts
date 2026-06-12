import { computed } from 'vue';
import type { Component } from 'vue';
import { blockRegistry, getBlockDefinition } from '@/pages/proposals/components/blocks/registry';
import type { BaseBlock, BlockType } from '@/types/proposal-builder';

export function useBlockRegistry() {
    const resolveComponent = (block: BaseBlock): Component => {
        const definition = getBlockDefinition(block.type);

        if (!definition) {
            throw new Error(`Unknown block type: ${block.type}`);
        }

        return definition.component;
    };

    const resolveSettingsComponent = (block: BaseBlock): Component => {
        const definition = getBlockDefinition(block.type);

        if (!definition) {
            throw new Error(`Unknown block type: ${block.type}`);
        }

        return definition.settings;
    };

    const getBlockLabel = (type: BlockType): string => {
        const definition = getBlockDefinition(type);

        return definition?.label || type;
    };

    const getBlockIcon = (type: BlockType): Component => {
        const definition = getBlockDefinition(type);

        if (!definition) {
            throw new Error(`Unknown block type: ${type}`);
        }

        return definition.icon;
    };

    const getBlocksByCategory = (category: string) => {
        return computed(() => 
            blockRegistry.filter(block => block.category === category)
        );
    };

    const getAllBlocks = () => {
        return computed(() => blockRegistry);
    };

    return {
        resolveComponent,
        resolveSettingsComponent,
        getBlockLabel,
        getBlockIcon,
        getBlocksByCategory,
        getAllBlocks,
        blockRegistry,
    };
}
