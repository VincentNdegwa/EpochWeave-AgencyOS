<script setup lang="ts">
import { LayoutIcon } from '@lucide/vue';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type { TimelineBlockData } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<TimelineBlockData>(props.blockId);
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- SECTION 1 — LAYOUT -->
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <LayoutIcon class="h-3 w-3" />
        Layout
      </p>

      <div class="grid grid-cols-2 gap-1.5">
        <button
          v-for="opt in [{ value: 'vertical', label: 'Vertical', hint: 'Stacked top to bottom' }, { value: 'horizontal', label: 'Horizontal', hint: 'Left to right flow' }]"
          :key="opt.value"
          type="button"
          class="flex flex-col items-center gap-1.5 rounded-lg border px-3 py-3 text-xs font-medium transition"
          :class="data.layout === opt.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          @click="updateData({ layout: opt.value as TimelineBlockData['layout'] })"
        >
          <!-- Icon preview -->
          <div class="flex gap-1" :class="opt.value === 'vertical' ? 'flex-col' : 'flex-row items-center'">
            <template v-for="n in 3" :key="n">
              <div class="flex items-center gap-1" :class="opt.value === 'vertical' ? 'flex-row' : 'flex-col'">
                <div class="h-2 w-2 rounded-full"
                  :class="data.layout === opt.value ? 'bg-primary' : 'bg-muted-foreground/40'" />
                <div class="rounded"
                  :class="[
                    opt.value === 'vertical' ? 'h-0.5 w-8' : 'h-4 w-0.5',
                    data.layout === opt.value ? 'bg-primary/30' : 'bg-muted-foreground/20',
                    n === 3 ? 'hidden' : '',
                  ]"
                />
              </div>
            </template>
          </div>
          {{ opt.label }}
          <span class="text-[10px] font-normal opacity-70">{{ opt.hint }}</span>
        </button>
      </div>

      <p class="mt-3 text-[11px] text-muted-foreground">
        Edit milestones directly on the canvas — click any field to update it.
      </p>
    </div>

  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>