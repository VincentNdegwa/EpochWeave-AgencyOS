<script setup lang="ts">
import { RulerIcon } from '@lucide/vue';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type { SpacerBlockData } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<SpacerBlockData>(props.blockId);

const heightOptions = [8, 16, 24, 32, 48, 64] as const;
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- SECTION 1 — HEIGHT -->
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <RulerIcon class="h-3 w-3" />
        Height
      </p>

      <div class="grid grid-cols-3 gap-1.5 mb-4">
        <button
          v-for="h in heightOptions"
          :key="h"
          type="button"
          class="flex flex-col items-center gap-1.5 rounded-md border py-2.5 text-xs font-medium transition"
          :class="data.height_px === h
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          @click="updateData({ height_px: h })"
        >
          <span
            class="block w-5 rounded-sm"
            :class="data.height_px === h ? 'bg-primary/40' : 'bg-muted-foreground/20'"
            :style="{ height: `${Math.max(2, h / 8)}px` }"
          />
          {{ h }}px
        </button>
      </div>

      <!-- Preview -->
      <div class="overflow-hidden rounded-md border border-border bg-muted/30">
        <div class="border-b border-dashed border-border/60 bg-background/50 px-3 py-1 text-[11px] text-muted-foreground">
          Canvas preview
        </div>
        <div class="px-4 py-2">
          <div class="w-full rounded bg-muted/50" :style="{ height: `${data.height_px}px` }" />
        </div>
      </div>
    </div>

  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>