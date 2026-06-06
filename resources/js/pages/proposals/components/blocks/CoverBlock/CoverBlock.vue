<script setup lang="ts">
import { computed } from 'vue';
import type { BaseBlock, CoverBlockData } from '@/types/proposal-builder';

const props = defineProps<{
  data: CoverBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const coverStyle = computed(() => {
  if (props.data.background_type === 'image') {
    return {
      backgroundImage: `url(${props.data.background_value})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      color: props.data.text_color,
    } as Record<string, string>;
  }

  return {
    backgroundColor: props.data.background_value,
    color: props.data.text_color,
  } as Record<string, string>;
});
</script>

<template>
  <section class="relative p-6 text-center text-foreground" :style="coverStyle">
    <div class="space-y-3">
      <p class="text-xs uppercase tracking-[0.25em] text-muted-foreground">Proposal</p>
      <h1 class="text-4xl font-bold">{{ props.data.heading }}</h1>
      <p v-if="props.data.subheading" class="text-lg opacity-80">{{ props.data.subheading }}</p>
    </div>

    <div class="mt-10 flex items-center justify-between text-sm">
      <div v-if="props.data.show_logo" class="text-left">
        <p class="text-xs uppercase">Workspace</p>
        <p class="text-base font-semibold">Your company</p>
      </div>
      <div class="text-right">
        <p v-if="props.data.show_proposal_number">Proposal #{{ props.block.id.slice(-6) }}</p>
        <p v-if="props.data.show_date">{{ new Date().toLocaleDateString() }}</p>
      </div>
    </div>
  </section>
</template>
