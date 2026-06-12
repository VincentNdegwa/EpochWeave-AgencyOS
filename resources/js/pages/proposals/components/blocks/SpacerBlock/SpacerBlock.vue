<script setup lang="ts">
import type { BaseBlock, SpacerBlockData } from '@/types/proposal-builder';

defineProps<{
  data: SpacerBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();
</script>

<template>
  <section :style="{ height: `${data.height_px}px` }" class="relative w-full">
    <!-- Edit mode label -->
    <div
      v-if="!isLocked"
      class="absolute inset-0 flex items-center justify-center"
    >
      <div class="flex items-center gap-2 rounded border border-dashed border-border px-3 py-1">
        <span class="text-[11px] text-muted-foreground">Spacer · {{ data.height_px }}px</span>
      </div>
    </div>
  </section>
</template>