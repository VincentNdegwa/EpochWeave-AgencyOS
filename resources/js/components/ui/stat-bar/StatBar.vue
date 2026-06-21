<script setup lang="ts">
import { TrendingDown, TrendingUp } from '@lucide/vue';
import { computed } from 'vue';

export interface StatItem {
    label: string;
    value: string | number;
    color?: string;
    change?: {
        value: number;
        label: string;
    };
}

const props = withDefaults(
    defineProps<{
        items: StatItem[];
        columns?: 2 | 3 | 4 | 5 | 6;
    }>(),
    {
        columns: undefined,
    },
);

const gridClass = computed(() => {
    const count = props.columns ?? props.items.length;

    const map: Record<number, string> = {
        1: 'grid-cols-1',
        2: 'grid-cols-2',
        3: 'grid-cols-2 sm:grid-cols-3',
        4: 'grid-cols-2 sm:grid-cols-4',
        5: 'grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
        6: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-6',
    };

    return map[count] ?? map[4];
});
</script>

<template>
    <div class="border-t border-b border-border bg-background print:hidden">
        <div class="grid divide-x divide-border" :class="gridClass">
            <div
                v-for="item in items"
                :key="item.label"
                class="px-6 py-4"
            >
                <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">
                    {{ item.label }}
                </p>
                <div class="mt-0.5 flex items-center gap-1.5">
                    <span
                        v-if="item.color"
                        class="h-2 w-2 rounded-full"
                        :style="{ backgroundColor: item.color }"
                    />
                    <p class="text-base font-bold text-foreground">
                        {{ item.value }}
                    </p>
                </div>
                <div v-if="item.change" class="mt-1 flex items-center gap-1.5 text-xs">
                    <span
                        :class="[
                            'inline-flex items-center gap-0.5 font-medium',
                            item.change.value >= 0 ? 'text-emerald-600' : 'text-rose-600',
                        ]"
                    >
                        <component
                            :is="item.change.value >= 0 ? TrendingUp : TrendingDown"
                            class="h-3.5 w-3.5"
                            :stroke-width="2"
                        />
                        {{ item.change.value >= 0 ? '+' : '' }}{{ item.change.value }}%
                    </span>
                    <span class="text-muted-foreground">{{ item.change.label }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
