<script setup lang="ts">
import { computed } from 'vue';
import type { BaseBlock, CoverBlockData } from '@/types/proposal-builder';
import { useWorkspaceStore } from '@/stores/workspace';

const props = defineProps<{
  data: CoverBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const workspaceStore = useWorkspaceStore();

const containerStyle = computed(() => {
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

const overlayVisible = computed(
  () => props.data.background_type === 'image',
);

const today = computed(() =>
  new Date().toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }),
);

const shortId = computed(() => props.block.id.slice(-6).toUpperCase());
</script>

<template>
  <section
    class="relative flex min-h-[400px] w-full flex-col px-6 overflow-hidden"
    :style="containerStyle"
  >
    <div
      v-if="overlayVisible"
      class="pointer-events-none absolute inset-0 bg-black/40"
    />

    <header class="relative z-10 flex items-start justify-between pt-10">
      <div v-if="data.show_logo" class="flex items-center gap-3">
        <img
          v-if="workspaceStore.logoUrl"
          :src="workspaceStore.logoUrl"
          alt="Workspace logo"
          class="h-9 w-auto object-contain"
        />
        <span
          v-else
          class="text-sm font-semibold tracking-wide opacity-90"
        >
          {{ workspaceStore.name ?? 'Your Company' }}
        </span>
      </div>
      <div v-else />

      <div class="text-right text-xs opacity-70 space-y-0.5">
        <p v-if="data.show_proposal_number" class="font-mono tracking-wider uppercase">
          Proposal #{{ shortId }}
        </p>
        <p v-if="data.show_date">{{ today }}</p>
      </div>
    </header>

    <div class="relative z-10 flex flex-1 flex-col items-start justify-center pb-14">
      <h1 class="max-w-2xl text-5xl font-bold leading-tight tracking-tight">
        {{ data.heading || 'Proposal Title' }}
      </h1>

      <p
        v-if="data.subheading"
        class="mt-4 max-w-xl text-lg leading-relaxed opacity-75"
      >
        {{ data.subheading }}
      </p>

      <div class="mt-8 h-[3px] w-16 rounded-full opacity-60" style="background: currentColor;" />
    </div>

  </section>
</template>