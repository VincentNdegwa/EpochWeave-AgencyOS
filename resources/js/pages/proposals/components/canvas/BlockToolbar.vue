<script setup lang="ts">
import {
    GripVertical,
    ArrowUp,
    ArrowDown,
    Copy,
    EyeOff,
    Trash2,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock } from '@/types/proposal-builder';

const props = defineProps<{
    block: BaseBlock;
    isSelected: boolean;
}>();

const store = useProposalBuilderStore();

const handleMove = (direction: 'up' | 'down') => {
    store.moveBlock(props.block.id, direction);
};

const handleDuplicate = () => {
    store.duplicateBlock(props.block.id);
};

const handleDelete = () => {
    store.deleteBlock(props.block.id);
};

const toggleVisibility = () => {
    store.updateBlockMeta(props.block.id, {
        ...props.block.meta,
        is_hidden: !props.block.meta.is_hidden,
    });
};
</script>

<template>
    <div
        v-if="isSelected"
        class="absolute -top-3 right-4 z-10 flex items-center gap-1 rounded-md border border-border bg-background/95 p-1 shadow-md"
    >
        <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7 cursor-grab text-muted-foreground"
        >
            <GripVertical class="h-3.5 w-3.5" />
        </Button>
        <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7"
            @click.stop="handleMove('up')"
        >
            <ArrowUp class="h-3.5 w-3.5" />
        </Button>
        <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7"
            @click.stop="handleMove('down')"
        >
            <ArrowDown class="h-3.5 w-3.5" />
        </Button>
        <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7"
            @click.stop="handleDuplicate"
        >
            <Copy class="h-3.5 w-3.5" />
        </Button>
        <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7"
            @click.stop="toggleVisibility"
        >
            <EyeOff class="h-3.5 w-3.5" />
        </Button>
        <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7 text-destructive"
            @click.stop="handleDelete"
        >
            <Trash2 class="h-3.5 w-3.5" />
        </Button>
    </div>
</template>
