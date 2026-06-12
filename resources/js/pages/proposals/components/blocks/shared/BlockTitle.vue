<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        placeholder?: string;
        isLocked?: boolean;
        color?: string | null;
    }>(),
    {
        placeholder: 'Add a title',
        isLocked: false,
        color: null,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

</script>

<template>
    <div>
        <Input
            v-if="!props.isLocked"
            :model-value="props.modelValue ?? ''"
            :placeholder="props.placeholder"
            @update:model-value="(value: string | number) => emit('update:modelValue', String(value) || null)"
            :class="cn(
                '-mx-2 cursor-text text-lg font-semibold hover:bg-muted/50 border-none bg-transparent h-auto',
                props.color ? { color: props.color } : undefined
            )"
            :style="props.color ? { color: props.color } : undefined"
        />
        <p
            v-else
            class="text-lg font-semibold"
            :style="props.color ? { color: props.color } : undefined"
        >
            {{ props.modelValue || props.placeholder }}
        </p>
    </div>
</template>
