<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { ColorPresets } from '@/components/ui/color-presets';
import { TextColorPicker } from '@/components/ui/text-color-picker';
import { TypeIcon, PaletteIcon, ToggleLeftIcon } from '@lucide/vue';
import type { RichTextBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{ blockId: string }>();

const store = useProposalBuilderStore();

const block = computed(() => store.blocks.find((b) => b.id === props.blockId) ?? null);
const data  = computed<RichTextBlockData | null>(
  () => (block.value?.data as RichTextBlockData) ?? null,
);

const updateData = (changes: Partial<RichTextBlockData>) => {
  if (!block.value || !data.value) return;
  store.updateBlockData(block.value.id, { ...data.value, ...changes });
};
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- ══════════════════════════════════════════════
         SECTION 1 — TITLE
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <TypeIcon class="h-3 w-3" />
        Section title
      </p>

      <!-- Show title toggle -->
      <label class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2.5
                    border border-border hover:bg-muted/50 transition-colors mb-3">
        <div>
          <p class="text-xs font-medium text-foreground">Show title</p>
          <p class="text-[11px] text-muted-foreground">Display a heading above the text</p>
        </div>
        <Switch
          :model-value="data.show_title ?? true"
          @update:model-value="(v: boolean) => updateData({ show_title: v })"
        />
      </label>

      <!-- Title input — only when show_title is on -->
      <template v-if="data.show_title ?? true">
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Title text</Label>
          <Input
            :model-value="data.title ?? ''"
            placeholder="e.g. Our Approach"
            class="h-8 text-sm"
            @update:model-value="(v) => updateData({ title: v ? String(v) : null })"
          />
        </div>
      </template>
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 2 — COLOURS
    ══════════════════════════════════════════════ -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <PaletteIcon class="h-3 w-3" />
        Colours
      </p>

      <!-- Title colour -->
      <div v-if="data.show_title ?? true" class="grid gap-2 mb-4">
        <Label class="text-xs text-muted-foreground">Title colour</Label>
        <TextColorPicker
          :model-value="data.title_color ?? '#111827'"
          light-color="#FFFFFF"
          dark-color="#111827"
          @update:model-value="(v) => updateData({ title_color: v })"
        />
      </div>

      <!-- Content colour -->
      <div class="grid gap-2 mb-4">
        <Label class="text-xs text-muted-foreground">Content colour</Label>
        <TextColorPicker
          :model-value="data.content_color ?? '#374151'"
          light-color="#FFFFFF"
          dark-color="#111827"
          @update:model-value="(v) => updateData({ content_color: v })"
        />
      </div>

      <!-- Background colour -->
      <div class="grid gap-2">
        <Label class="text-xs text-muted-foreground">Block background</Label>
        <ColorPresets
          :model-value="data.background_color ?? 'transparent'"
          placeholder="transparent"
          :supports-transparent="true"
          @update:model-value="(v) => updateData({ background_color: v })"
        />
        <p class="text-[11px] text-muted-foreground mt-0.5">
          Set a background to visually separate this section from others.
        </p>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECTION 3 — DISPLAY OPTIONS
    ══════════════════════════════════════════════ -->
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <ToggleLeftIcon class="h-3 w-3" />
        Display
      </p>

      <div class="space-y-0 divide-y divide-border rounded-lg border border-border overflow-hidden">
        <!-- Full width toggle -->
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5
                      hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Full width text</p>
            <p class="text-[11px] text-muted-foreground">Remove max-width constraint on content</p>
          </div>
          <Switch
            :model-value="data.full_width ?? false"
            @update:model-value="(v: boolean) => updateData({ full_width: v })"
          />
        </label>
      </div>
    </div>

  </div>

  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>