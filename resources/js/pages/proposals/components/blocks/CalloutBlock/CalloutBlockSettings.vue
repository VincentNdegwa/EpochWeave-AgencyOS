<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { ColorPresets } from '@/components/ui/color-presets';
import { PaletteIcon, LayoutIcon } from '@lucide/vue';
import type { CalloutBlockData } from '@/types/proposal-builder';
import { useBlockSettings } from '@/composables/useBlockSettings';

const props = defineProps<{ blockId: string }>();

const { block, data, updateData } = useBlockSettings<CalloutBlockData>(props.blockId);

// Preset callout styles
const presets = [
  { label: 'Info',    bg: '#EFF6FF', accent: '#3B82F6' },
  { label: 'Success', bg: '#F0FDF4', accent: '#22C55E' },
  { label: 'Warning', bg: '#FFFBEB', accent: '#F59E0B' },
  { label: 'Quote',   bg: '#F8FAFC', accent: '#6366F1' },
  { label: 'Tip',     bg: '#FEFCE8', accent: '#EAB308' },
  { label: 'Custom',  bg: null,      accent: null       },
] as const;

const applyPreset = (preset: typeof presets[number]) => {
  if (!preset.bg) return;
  updateData({
    background_color: preset.bg,
    accent_color:     preset.accent ?? '#6366f1',
    border_left:      true,
  });
};
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- ══════ SECTION 1 — PRESETS ══════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <LayoutIcon class="h-3 w-3" />
        Quick styles
      </p>

      <div class="grid grid-cols-3 gap-1.5">
        <button
          v-for="preset in presets"
          :key="preset.label"
          type="button"
          class="flex flex-col items-center gap-1 rounded-lg border px-2 py-2.5
                 text-xs font-medium transition"
          :class="preset.bg && data.background_color === preset.bg
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          :style="preset.bg ? { borderLeftColor: preset.accent ?? '', borderLeftWidth: '3px' } : {}"
          @click="applyPreset(preset)"
        >
          <span class="text-base leading-none">📝</span>
          {{ preset.label }}
        </button>
      </div>
    </div>

    <!-- ══════ SECTION 3 — COLOURS ══════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                tracking-wider text-muted-foreground">
        <PaletteIcon class="h-3 w-3" />
        Colours
      </p>

      <div class="space-y-4">
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Background</Label>
          <ColorPresets
            :model-value="data.background_color ?? 'transparent'"
            placeholder="transparent"
            @update:model-value="(v) => updateData({ background_color: v })"
          />
        </div>

        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Accent color</Label>
          <ColorPresets
            :model-value="data.accent_color ?? '#6366f1'"
            placeholder="#6366f1"
            @update:model-value="(v) => updateData({ accent_color: v })"
          />
        </div>
      </div>
    </div>

    <!-- ══════ SECTION 4 — BORDER ══════ -->
    <div class="py-3">
      <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        Border
      </p>

      <div class="space-y-0 divide-y divide-border rounded-lg border border-border overflow-hidden">
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5
                      hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Left accent border</p>
            <p class="text-[11px] text-muted-foreground">Colored bar on the left edge</p>
          </div>
          <Switch
            :model-value="data.border_left ?? false"
            @update:model-value="(v: boolean) => updateData({ border_left: v })"
          />
        </label>
      </div>
    </div>

  </div>

  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>