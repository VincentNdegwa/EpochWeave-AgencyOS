<script setup lang="ts">
import { computed } from 'vue';
import { LayoutIcon } from '@lucide/vue';
import type { TestimonialBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{ blockId: string }>();
const store = useProposalBuilderStore();

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data  = computed<TestimonialBlockData | null>(() => (block.value?.data as TestimonialBlockData) ?? null);

const updateData = (changes: Partial<TestimonialBlockData>) => {
  if (!block.value || !data.value) return;
  store.updateBlockData(block.value.id, { ...data.value, ...changes });
};
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <LayoutIcon class="h-3 w-3" />
        Layout
      </p>
      <div class="grid grid-cols-2 gap-1.5">
        <button
          v-for="opt in [{ value: 'single', label: 'Single', hint: 'One large quote' }, { value: 'grid', label: 'Grid', hint: '2-column cards' }]"
          :key="opt.value"
          type="button"
          class="flex flex-col items-center gap-1.5 rounded-lg border px-3 py-3 text-xs font-medium transition"
          :class="data.layout === opt.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          @click="updateData({ layout: opt.value as TestimonialBlockData['layout'] })"
        >
          <!-- Mini preview -->
          <div v-if="opt.value === 'single'" class="flex flex-col items-center gap-1 w-12">
            <div class="h-1 w-full rounded" :class="data.layout === 'single' ? 'bg-primary/30' : 'bg-muted-foreground/20'" />
            <div class="h-1 w-4/5 rounded" :class="data.layout === 'single' ? 'bg-primary/20' : 'bg-muted-foreground/10'" />
            <div class="h-1 w-3/5 rounded" :class="data.layout === 'single' ? 'bg-primary/20' : 'bg-muted-foreground/10'" />
          </div>
          <div v-else class="grid grid-cols-2 gap-1 w-12">
            <div v-for="n in 2" :key="n" class="rounded border"
              :class="data.layout === 'grid' ? 'border-primary/30 bg-primary/5' : 'border-muted-foreground/20 bg-muted/30'"
              style="height:24px" />
          </div>
          {{ opt.label }}
          <span class="text-[10px] font-normal opacity-70">{{ opt.hint }}</span>
        </button>
      </div>
      <p class="mt-3 text-[11px] text-muted-foreground">Edit quotes and authors directly on the canvas.</p>
    </div>
  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>