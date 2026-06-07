<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    modelValue: string;
    lightColor?: string;
    darkColor?: string;
  }>(),
  {
    lightColor: '#FFFFFF',
    darkColor: '#0F172A',
  }
);

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const isLight = computed(() => props.modelValue === props.lightColor);
const isDark = computed(() => props.modelValue === props.darkColor);

const handleColorInput = (e: Event) => {
  const value = (e.target as HTMLInputElement).value;
  emit('update:modelValue', value);
};

const setLight = () => {
  emit('update:modelValue', props.lightColor);
};

const setDark = () => {
  emit('update:modelValue', props.darkColor);
};
</script>

<template>
  <div class="flex items-center gap-3">
    <button
      type="button"
      class="flex h-9 flex-1 items-center justify-center gap-2 rounded-md border text-xs font-medium transition"
      :class="isLight
        ? 'border-primary bg-primary text-primary-foreground'
        : 'border-border bg-background text-foreground hover:bg-muted'"
      @click="setLight"
    >
      <span class="inline-block h-3 w-3 rounded-full bg-white border border-border" />
      Light
    </button>
    <button
      type="button"
      class="flex h-9 flex-1 items-center justify-center gap-2 rounded-md border text-xs font-medium transition"
      :class="isDark
        ? 'border-primary bg-primary text-primary-foreground'
        : 'border-border bg-background text-foreground hover:bg-muted'"
      @click="setDark"
    >
      <span class="inline-block h-3 w-3 rounded-full bg-slate-900 border border-border" />
      Dark
    </button>

    <div class="relative shrink-0">
      <div
        class="h-9 w-9 rounded-md border border-border cursor-pointer overflow-hidden shadow-sm"
        :style="{ backgroundColor: modelValue }"
      >
        <input
          type="color"
          :value="modelValue"
          class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
          @input="handleColorInput"
        />
      </div>
    </div>
  </div>
</template>
