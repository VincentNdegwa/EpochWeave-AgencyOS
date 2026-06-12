<script setup lang="ts">
import { LayoutIcon, AlignCenterVerticalIcon } from '@lucide/vue';
import { computed } from 'vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { ColumnBlockData } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();
const store = useProposalBuilderStore();

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data  = computed<ColumnBlockData | null>(() => (block.value?.data as ColumnBlockData) ?? null);

const updateData = (changes: Partial<ColumnBlockData>) => {
  if (!block.value || !data.value) {
return;
}

  store.updateBlockData(block.value.id, { ...data.value, ...changes });
};

// When changing column count, resize children array
const setColumns = (count: 1 | 2 | 3) => {
  if (!data.value) {
return;
}

  const current = data.value.children;
  let children: any[][]

  if (count > current.length) {
    // Add empty columns
    children = [...current, ...Array.from({ length: count - current.length }, () => [])]
  } else {
    // Merge excess column content into the last kept column
    const kept   = current.slice(0, count - 1);
    const merged = current.slice(count - 1).flat();
    children     = [...kept, merged];
  }

  updateData({ columns: count, children, column_widths: undefined });
};

const columnOptions = [
  { value: 1, label: '1 col',  hint: 'Full width' },
  { value: 2, label: '2 cols', hint: 'Side by side' },
  { value: 3, label: '3 cols', hint: 'Three equal' },
] as const;

const gapOptions = [
  { value: 'sm', label: 'Tight',   px: '12px' },
  { value: 'md', label: 'Normal',  px: '24px' },
  { value: 'lg', label: 'Wide',    px: '40px' },
] as const;

const alignOptions = [
  { value: 'start',  label: 'Top' },
  { value: 'center', label: 'Middle' },
  { value: 'end',    label: 'Bottom' },
] as const;

// 2-col custom width ratios
const ratioPresets = [
  { label: '50 / 50', widths: [50, 50] },
  { label: '60 / 40', widths: [60, 40] },
  { label: '40 / 60', widths: [40, 60] },
  { label: '70 / 30', widths: [70, 30] },
  { label: '30 / 70', widths: [30, 70] },
] as const;
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- ══════ SECTION 1 — COLUMNS ══════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <LayoutIcon class="h-3 w-3" />
        Columns
      </p>

      <div class="grid grid-cols-3 gap-1.5">
        <button
          v-for="opt in columnOptions"
          :key="opt.value"
          type="button"
          class="flex flex-col items-center gap-1.5 rounded-lg border px-2 py-3
                 text-xs font-medium transition"
          :class="data.columns === opt.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          @click="setColumns(opt.value)"
        >
          <!-- Mini column preview -->
          <div class="flex gap-0.5">
            <div
              v-for="n in opt.value"
              :key="n"
              class="h-5 rounded-sm"
              :class="data.columns === opt.value ? 'bg-primary/40' : 'bg-muted-foreground/25'"
              :style="{ width: opt.value === 1 ? '28px' : opt.value === 2 ? '13px' : '8px' }"
            />
          </div>
          {{ opt.label }}
          <span class="text-[10px] font-normal opacity-70">{{ opt.hint }}</span>
        </button>
      </div>
    </div>

    <!-- ══════ SECTION 2 — COLUMN WIDTHS (2-col only) ══════ -->
    <div v-if="data.columns === 2" class="py-3 border-b border-border">
      <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        Column ratio
      </p>

      <div class="grid grid-cols-2 gap-1 mb-2">
        <button
          v-for="preset in ratioPresets"
          :key="preset.label"
          type="button"
          class="rounded-md border py-1.5 text-center text-xs font-medium transition"
          :class="JSON.stringify(data.column_widths) === JSON.stringify(preset.widths)
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
          @click="updateData({ column_widths: [...preset.widths] })"
        >
          {{ preset.label }}
        </button>
      </div>

      <!-- Visual ratio bar -->
      <div
        v-if="data.column_widths"
        class="mt-3 flex overflow-hidden rounded-md border border-border"
      >
        <div
          class="flex items-center justify-center py-1.5 text-[11px] font-medium text-primary bg-primary/10"
          :style="{ width: `${data.column_widths[0]}%` }"
        >
          {{ data.column_widths[0] }}%
        </div>
        <div
          class="flex flex-1 items-center justify-center py-1.5 text-[11px] font-medium text-muted-foreground bg-muted/50"
        >
          {{ data.column_widths[1] }}%
        </div>
      </div>
    </div>

    <!-- ══════ SECTION 3 — GAP ══════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        Column gap
      </p>

      <div class="grid grid-cols-3 gap-1">
        <button
          v-for="opt in gapOptions"
          :key="opt.value"
          type="button"
          class="flex flex-col items-center gap-1 rounded-md border py-2 text-xs
                 font-medium transition"
          :class="(data.gap ?? 'md') === opt.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
          @click="updateData({ gap: opt.value })"
        >
          <div class="flex gap-0.5 items-center">
            <div class="h-4 w-2 rounded-sm" :class="(data.gap ?? 'md') === opt.value ? 'bg-primary/40' : 'bg-muted-foreground/25'" />
            <div
              class="rounded-sm"
              :class="(data.gap ?? 'md') === opt.value ? 'bg-primary/20' : 'bg-muted-foreground/10'"
              :style="{
                width: opt.value === 'sm' ? '2px' : opt.value === 'md' ? '5px' : '9px',
                height: '16px',
              }"
            />
            <div class="h-4 w-2 rounded-sm" :class="(data.gap ?? 'md') === opt.value ? 'bg-primary/40' : 'bg-muted-foreground/25'" />
          </div>
          {{ opt.label }}
          <span class="text-[10px] font-normal opacity-70">{{ opt.px }}</span>
        </button>
      </div>
    </div>

    <!-- ══════ SECTION 4 — VERTICAL ALIGN ══════ -->
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <AlignCenterVerticalIcon class="h-3 w-3" />
        Vertical alignment
      </p>

      <div class="grid grid-cols-3 gap-1">
        <button
          v-for="opt in alignOptions"
          :key="opt.value"
          type="button"
          class="rounded-md border py-1.5 text-center text-xs font-medium transition"
          :class="(data.vertical_align ?? 'start') === opt.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
          @click="updateData({ vertical_align: opt.value })"
        >
          {{ opt.label }}
        </button>
      </div>
      <p class="mt-2 text-[11px] text-muted-foreground">
        Controls how columns align vertically when they have different heights.
      </p>
    </div>

  </div>

  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>