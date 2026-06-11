<script setup lang="ts">
import { computed } from 'vue';
import { FileTextIcon } from '@lucide/vue';
import type { TermsBlockData } from '@/types/proposal-builder';
import { useBlockSettings } from '@/composables/useBlockSettings';

const props = defineProps<{ blockId: string }>();

const { block, data, updateData } = useBlockSettings<TermsBlockData>(props.blockId);
</script>

<template>
  <div v-if="data" class="flex flex-col gap-0 text-sm">
    <div class="py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <FileTextIcon class="h-3 w-3" />
        About this block
      </p>
      <div class="rounded-lg border border-border bg-muted/30 px-3 py-3 text-xs text-muted-foreground space-y-1.5">
        <p>The Terms block displays your legal terms and conditions.</p>
        <p>Edit the content directly on the canvas using the rich text editor.</p>
        <p>This block is typically placed before the Signature block.</p>
      </div>
    </div>
  </div>
  <p v-else class="py-3 text-sm text-muted-foreground">Block data unavailable.</p>
</template>