<script setup lang="ts">
const props = defineProps<{
  afterBlockId: string | null;
}>();

const emit = defineEmits<{
  (e: 'request-add', afterBlockId: string | null): void;
}>();
</script>

<template>
  <div class="group -mx-2 flex items-center px-2 py-1">
    <div class="flex-1 border-t border-dashed border-border/60 transition-colors group-hover:border-primary" />
    <button
      type="button"
      class="mx-2 hidden rounded-full border border-border bg-background p-1 text-sm text-muted-foreground transition group-hover:flex group-hover:text-primary"
      @click="emit('request-add', props.afterBlockId)"
    >
      +
    </button>
    <div class="flex-1 border-t border-dashed border-border/60 transition-colors group-hover:border-primary" />
  </div>
</template>
