<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  isDirty: boolean;
  isSaving: boolean;
}>();

const statusLabel = computed(() => {
  if (props.isSaving) {
    return 'Saving…';
  }

  if (props.isDirty) {
    return 'Unsaved changes';
  }

  return 'Saved';
});

const toneClass = computed(() => {
  if (props.isSaving) {
    return 'text-primary';
  }

  if (props.isDirty) {
    return 'text-amber-500';
  }

  return 'text-muted-foreground';
});
</script>

<template>
  <p class="text-xs" :class="toneClass">{{ statusLabel }}</p>
</template>
