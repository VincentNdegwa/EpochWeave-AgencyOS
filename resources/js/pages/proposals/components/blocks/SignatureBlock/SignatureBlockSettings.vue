<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { PenLineIcon, ToggleLeftIcon } from '@lucide/vue';
import type { SignatureBlockData } from '@/types/proposal-builder';
import { useBlockSettings } from '@/composables/useBlockSettings';

const props = defineProps<{ blockId: string }>();

const { block, data, updateData } = useBlockSettings<SignatureBlockData>(props.blockId);
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">

    <!-- SECTION 1 — CONTENT -->
    <div class="py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <PenLineIcon class="h-3 w-3" />
        Content
      </p>
      <div class="space-y-3">
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Title</Label>
          <Input
            :model-value="data.title ?? ''"
            placeholder="Authorization & Sign-Off"
            class="h-8 text-sm"
            @update:model-value="(v) => updateData({ title: v ? String(v) : null })"
          />
        </div>
        <div class="grid gap-1.5">
          <Label class="text-xs text-muted-foreground">Description</Label>
          <Input
            :model-value="data.description ?? ''"
            placeholder="Please sign below to accept this proposal"
            class="h-8 text-xs"
            @update:model-value="(v) => updateData({ description: v ? String(v) : null })"
          />
        </div>
      </div>
    </div>

    <!-- SECTION 2 — REQUIRED FIELDS -->
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <ToggleLeftIcon class="h-3 w-3" />
        Required fields
      </p>

      <div class="space-y-0 divide-y divide-border rounded-lg border border-border overflow-hidden">
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5 hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Require full name</p>
            <p class="text-[11px] text-muted-foreground">Client must type their name</p>
          </div>
          <Switch :model-value="data.require_name" @update:model-value="(v: boolean) => updateData({ require_name: v })" />
        </label>
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5 hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Require date</p>
            <p class="text-[11px] text-muted-foreground">Client confirms the signing date</p>
          </div>
          <Switch :model-value="data.require_date" @update:model-value="(v: boolean) => updateData({ require_date: v })" />
        </label>
        <label class="flex cursor-pointer items-center justify-between px-3 py-2.5 hover:bg-muted/50 transition-colors">
          <div>
            <p class="text-xs font-medium text-foreground">Require drawn signature</p>
            <p class="text-[11px] text-muted-foreground">Canvas signature pad</p>
          </div>
          <Switch :model-value="data.require_signature" @update:model-value="(v: boolean) => updateData({ require_signature: v })" />
        </label>
      </div>
    </div>

  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>