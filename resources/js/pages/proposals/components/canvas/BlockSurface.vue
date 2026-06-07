<script setup lang="ts">
import { computed } from 'vue';
import type { BlockMeta } from '@/types/proposal-builder';

const props = defineProps<{
  meta: BlockMeta;
  isLocked: boolean;
}>();

const paddingScale: Record<BlockMeta['padding_top'], number> = {
  none: 0,
  sm: 12,
  md: 24,
  lg: 40,
  xl: 64,
};

const paddingTop = computed(() => `${paddingScale[props.meta.padding_top]}px`);
const paddingBottom = computed(() => `${paddingScale[props.meta.padding_bottom]}px`);
const backgroundColor = computed(() => props.meta.background_color ?? 'transparent');

const surfaceStyle = computed(() => ({
  backgroundColor: backgroundColor.value,
  paddingTop: paddingTop.value,
  paddingBottom: paddingBottom.value,
  '--block-meta-padding-top': paddingTop.value,
  '--block-meta-padding-bottom': paddingBottom.value,
  '--block-meta-background': backgroundColor.value,
}));

const surfaceClasses = computed(() => [
  'relative bg-background transition-all',
  props.meta.border_top ? 'border-t border-border' : '',
  props.meta.border_bottom ? 'border-b border-border' : '',
]);

const isHiddenInPreview = computed(() => props.isLocked && props.meta.is_hidden);
const isHiddenInEditor = computed(() => !props.isLocked && props.meta.is_hidden);
</script>

<template>
  <div v-if="!isHiddenInPreview" :class="surfaceClasses" :style="surfaceStyle">
    <slot />

    <div
      v-if="isHiddenInEditor"
      class="pointer-events-none absolute inset-0 bg-background/60 text-center text-xs font-medium
             text-muted-foreground backdrop-blur-sm flex items-center justify-center"
    >
      Hidden from client preview
    </div>
  </div>

  <div
    v-else
    class="rounded border border-dashed border-border bg-muted/40 px-6 py-8 text-center text-xs font-medium text-muted-foreground"
  >
    Block hidden in client preview
  </div>
</template>
