<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { VideoIcon, LinkIcon } from '@lucide/vue';
import type { VideoEmbedBlockData } from '@/types/proposal-builder';
import { useBlockSettings } from '@/composables/useBlockSettings';

const props = defineProps<{ blockId: string }>();

const { block, data, updateData } = useBlockSettings<VideoEmbedBlockData>(props.blockId);

const platformOptions = [
  { value: 'youtube', label: 'YouTube' },
  { value: 'vimeo',   label: 'Vimeo' },
  { value: 'custom',  label: 'Custom' },
] as const;
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- SECTION 1 — VIDEO SOURCE -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <VideoIcon class="h-3 w-3" />
        Video source
      </p>

      <div class="space-y-3">
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Platform</Label>
          <div class="grid grid-cols-3 gap-1">
            <button
              v-for="opt in platformOptions"
              :key="opt.value"
              type="button"
              class="rounded-md border py-1.5 text-center text-xs font-medium transition"
              :class="data.platform === opt.value
                ? 'border-primary bg-primary/5 text-primary'
                : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
              @click="updateData({ platform: opt.value })"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <div class="grid gap-1.5">
          <Label class="flex items-center gap-1 text-xs text-muted-foreground">
            <LinkIcon class="h-3 w-3" /> Video URL
          </Label>
          <Input
            type="url"
            :model-value="data.url"
            :placeholder="data.platform === 'youtube'
              ? 'https://youtube.com/watch?v=…'
              : data.platform === 'vimeo'
                ? 'https://vimeo.com/…'
                : 'https://…'"
            class="h-8 text-xs"
            @update:model-value="(v) => updateData({ url: String(v ?? '') })"
          />
        </div>
      </div>
    </div>

    <!-- SECTION 2 — METADATA -->
    <div class="py-3">
      <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        Metadata
      </p>
      <div class="space-y-3">
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Title <span class="opacity-50">(optional)</span></Label>
          <Input
            :model-value="data.title ?? ''"
            placeholder="Video title"
            class="h-8 text-xs"
            @update:model-value="(v) => updateData({ title: v ? String(v) : null })"
          />
        </div>
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Caption <span class="opacity-50">(optional)</span></Label>
          <Input
            :model-value="data.caption ?? ''"
            placeholder="Short description"
            class="h-8 text-xs"
            @update:model-value="(v) => updateData({ caption: v ? String(v) : null })"
          />
        </div>
      </div>
    </div>

  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>