<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    message?: string;
    actionLabel?: string;
  }>(),
  {
    message: 'No content yet',
    actionLabel: 'Add content',
  }
);

const emit = defineEmits<{
  (e: 'action'): void;
}>();
</script>

<template>
  <div class="flex flex-col items-center rounded-lg border border-dashed border-border px-6 py-8 text-center text-muted-foreground">
    <p class="text-sm">{{ props.message }}</p>
    <button type="button" class="mt-3 text-xs font-medium text-primary" @click="emit('action')">
      {{ props.actionLabel }}
    </button>
  </div>
</template>
