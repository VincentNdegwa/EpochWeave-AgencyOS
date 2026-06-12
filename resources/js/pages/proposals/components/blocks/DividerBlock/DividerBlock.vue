<script setup lang="ts">
import { computed } from 'vue';
import type { CSSProperties } from 'vue';
import type { BaseBlock, DividerBlockData } from '@/types/proposal-builder';

const props = defineProps<{
    data: DividerBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const hrStyle = computed<CSSProperties>(() => ({
    borderTopStyle: props.data.style === 'none' ? 'none' : props.data.style,
    borderTopWidth:
        props.data.style === 'none' ? '0' : `${props.data.thickness_px}px`,
    borderTopColor: props.data.color,
}));
</script>

<template>
    <section class="px-8 py-2">
        <hr :style="hrStyle" class="w-full border-0" />
        <!-- Edit mode indicator -->
        <div
            v-if="!isLocked && data.style === 'none'"
            class="flex items-center justify-center py-1"
        >
            <span class="text-[11px] text-muted-foreground"
                >Invisible divider</span
            >
        </div>
    </section>
</template>
