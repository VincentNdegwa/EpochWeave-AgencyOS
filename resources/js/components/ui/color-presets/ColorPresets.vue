<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip';

interface Swatch {
  value: string;
  label: string;
}

const defaultSwatches: Swatch[] = [
  { value: '#0F172A', label: 'Slate 900' },
  { value: '#1E293B', label: 'Slate 800' },
  { value: '#0F766E', label: 'Teal 700' },
  { value: '#1D4ED8', label: 'Blue 700' },
  { value: '#7C3AED', label: 'Violet 600' },
  { value: '#BE185D', label: 'Pink 700' },
  { value: '#B45309', label: 'Amber 700' },
  { value: '#FFFFFF', label: 'White' },
];

const props = withDefaults(
  defineProps<{
    modelValue: string;
    swatches?: Swatch[];
    placeholder?: string;
    supportsTransparent?: boolean;
  }>(),
  {
    placeholder: '#000000',
    supportsTransparent: false,
  }
);

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const handleColorInput = (e: Event) => {
  const value = (e.target as HTMLInputElement).value;
  emit('update:modelValue', value);
};

const displayColor = computed(() => {
  if (props.supportsTransparent && props.modelValue === 'transparent') {
    return '#ffffff';
  }
  return props.modelValue;
});

const displayValue = computed(() => {
  if (props.supportsTransparent && props.modelValue === 'transparent') {
    return 'transparent';
  }
  return props.modelValue;
});

const swatchesToUse = computed(() => props.swatches ?? defaultSwatches);

const handleSwatchClick = (value: string) => {
  emit('update:modelValue', value);
};
</script>

<template>
  <div class="flex items-center gap-3">
    <div class="relative shrink-0">
      <div
        class="h-9 w-9 rounded-md border border-border shadow-sm cursor-pointer overflow-hidden"
        :style="supportsTransparent && modelValue === 'transparent'
          ? {}
          : { backgroundColor: modelValue }"
      >
        <div
          v-if="supportsTransparent && modelValue === 'transparent'"
          class="absolute inset-0"
          style="background-image: repeating-conic-gradient(#ddd 0% 25%, #fff 0% 50%) 0 0 / 8px 8px;"
        />
        <input
          type="color"
          :value="displayColor"
          class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
          @input="handleColorInput"
        />
      </div>
    </div>
    <Input
      :model-value="displayValue"
      :placeholder="placeholder"
      class="h-9 font-mono text-xs flex-1"
      @update:model-value="(v: string | number) => emit('update:modelValue', String(v ?? placeholder))"
    />
  </div>

  <div v-if="swatchesToUse.length > 0" class="mt-3">
    <p class="mb-2 text-[11px] text-muted-foreground">Presets</p>
    <div class="flex flex-wrap gap-2">
      <Tooltip v-for="swatch in swatchesToUse" :key="swatch.value">
        <TooltipTrigger as-child>
          <button
            type="button"
            class="h-6 w-6 rounded-md border-2 transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-ring relative overflow-hidden"
            :style="swatch.value !== 'transparent' ? { backgroundColor: swatch.value } : {}"
            :class="modelValue === swatch.value
              ? 'border-primary scale-110'
              : 'border-border'"
            @click="handleSwatchClick(swatch.value)"
          >
            <span
              v-if="swatch.value === 'transparent'"
              class="absolute inset-0"
              style="background-image: repeating-conic-gradient(#ddd 0% 25%, #fff 0% 50%) 0 0 / 6px 6px;"
            />
          </button>
        </TooltipTrigger>
        <TooltipContent side="bottom" class="text-xs">{{ swatch.label }}</TooltipContent>
      </Tooltip>
    </div>
  </div>
</template>
