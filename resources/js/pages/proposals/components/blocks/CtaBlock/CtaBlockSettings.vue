<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MousePointerClickIcon, AlignCenterIcon } from '@lucide/vue';
import type { CtaBlockData } from '@/types/proposal-builder';
import { useBlockSettings } from '@/composables/useBlockSettings';

const props = defineProps<{ blockId: string }>();

const { block, data, updateData } = useBlockSettings<CtaBlockData>(props.blockId);

const alignOptions = [
  { value: 'left',   label: 'Left' },
  { value: 'center', label: 'Center' },
  { value: 'right',  label: 'Right' },
] as const;
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- SECTION 1 — CONTENT -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <MousePointerClickIcon class="h-3 w-3" />
        Button
      </p>
      <div class="space-y-3">
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Button text</Label>
          <Input
            :model-value="data.button_text"
            placeholder="Contact Us"
            class="h-8 text-sm"
            @update:model-value="(v) => updateData({ button_text: String(v ?? 'Contact Us') })"
          />
        </div>
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Button link</Label>
          <Input
            type="url"
            :model-value="data.button_link"
            placeholder="https://… or mailto:…"
            class="h-8 text-xs"
            @update:model-value="(v) => updateData({ button_link: String(v ?? '#') })"
          />
        </div>
      </div>
    </div>

    <!-- SECTION 2 — ALIGNMENT -->
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <AlignCenterIcon class="h-3 w-3" />
        Alignment
      </p>
      <div class="grid grid-cols-3 gap-1">
        <button
          v-for="opt in alignOptions"
          :key="opt.value"
          type="button"
          class="rounded-md border py-1.5 text-center text-xs font-medium transition"
          :class="data.alignment === opt.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
          @click="updateData({ alignment: opt.value })"
        >
          {{ opt.label }}
        </button>
      </div>
      <p class="mt-2 text-[11px] text-muted-foreground">Edit heading and description directly on the canvas.</p>
    </div>

  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>