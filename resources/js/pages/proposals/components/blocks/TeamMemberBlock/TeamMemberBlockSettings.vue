<script setup lang="ts">
import { computed } from 'vue';
import { LayoutIcon } from '@lucide/vue';
import type { TeamMemberBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{ blockId: string }>();
const store = useProposalBuilderStore();

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data  = computed<TeamMemberBlockData | null>(() => (block.value?.data as TeamMemberBlockData) ?? null);

const updateData = (changes: Partial<TeamMemberBlockData>) => {
  if (!block.value || !data.value) return;
  store.updateBlockData(block.value.id, { ...data.value, ...changes });
};
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
          v-for="opt in [{ value: 'grid', label: 'Grid', hint: 'Cards in columns' }, { value: 'list', label: 'List', hint: 'Rows with detail' }]"
          :key="opt.value"
          type="button"
          class="flex flex-col items-center gap-1.5 rounded-lg border px-3 py-3 text-xs font-medium transition"
          :class="data.layout === opt.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          @click="updateData({ layout: opt.value as TeamMemberBlockData['layout'] })"
        >
          <!-- Mini preview -->
          <div v-if="opt.value === 'grid'" class="grid grid-cols-3 gap-1">
            <div v-for="n in 3" :key="n" class="flex flex-col items-center gap-0.5">
              <div class="h-4 w-4 rounded-full" :class="data.layout === 'grid' ? 'bg-primary/40' : 'bg-muted-foreground/20'" />
              <div class="h-1 w-6 rounded" :class="data.layout === 'grid' ? 'bg-primary/20' : 'bg-muted-foreground/10'" />
            </div>
          </div>
          <div v-else class="flex flex-col gap-1 w-16">
            <div v-for="n in 2" :key="n" class="flex items-center gap-1">
              <div class="h-4 w-4 flex-shrink-0 rounded-full" :class="data.layout === 'list' ? 'bg-primary/40' : 'bg-muted-foreground/20'" />
              <div class="h-1 flex-1 rounded" :class="data.layout === 'list' ? 'bg-primary/20' : 'bg-muted-foreground/10'" />
            </div>
          </div>
          {{ opt.label }}
          <span class="text-[10px] font-normal opacity-70">{{ opt.hint }}</span>
        </button>
      </div>

      <p class="mt-3 text-[11px] text-muted-foreground">
        Edit names, roles, bios, and avatars directly on the canvas.
      </p>
    </div>

  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>