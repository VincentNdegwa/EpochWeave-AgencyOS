<script setup lang="ts">
import { TrendingDown as IconTrendingDown, TrendingUp as IconTrendingUp } from '@lucide/vue';
import type { Component } from 'vue';
import { computed, onMounted, ref } from 'vue';

type StatsCardVariant = 'simple' | 'time' | 'company';

type Change = {
    value: number;
    label: string;
};

type Engagement = {
    value: number;
    label: string;
};

type Sparkline = {
    data: number[];
    color?: string;
};

type Props = {
    variant?: StatsCardVariant;
    title: string;
    titleSuffix?: string;
    value: string | number;
    change?: Change;
    icon?: Component;
    description?: string;
    footnote?: string;
    engagement?: Engagement;
    chart?: Component;
    sparkline?: Sparkline;
};

const props = withDefaults(defineProps<Props>(), {
    variant: 'simple',
});

const ApexChart = ref<Component | null>(null);

onMounted(async () => {
    const module = await import('vue3-apexcharts');
    ApexChart.value = module.default;
});

const isPositiveChange = computed(() => props.change && props.change.value >= 0);
const hasChart = computed(() => (props.sparkline && props.sparkline.data.length > 0) || !!props.chart);

const sparklineSeries = computed(() => [{ data: props.sparkline?.data ?? [] }]);

const sparklineOptions = computed(() => {
    return {
        chart: {
            type: 'line',
            sparkline: { enabled: true },
            toolbar: { show: false },
            animations: { enabled: true },
        },
        stroke: {
            curve: 'smooth',
            width: 2,
        },
        colors: [props.sparkline?.color ?? 'hsl(var(--primary))'],
        tooltip: { enabled: false },
        grid: { show: false },
        xaxis: {
            labels: { show: false },
            axisBorder: { show: false },
            axisTicks: { show: false },
            crosshairs: { show: false },
        },
        yaxis: { show: false },
        dataLabels: { enabled: false },
    };
});
</script>

<template>
    <div class="rounded-sm border bg-card px-4 py-4 shadow-sm">
        <!-- Simple variant -->
        <template v-if="variant === 'simple'">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div>
                        <span class="text-md font-medium text-foreground">{{ title }}</span>
                        <span v-if="titleSuffix" class="text-muted-foreground/70"> {{ titleSuffix }}</span>
                    </div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight">{{ value }}</div>

                    <div v-if="change" class="mt-3 flex items-center gap-2 text-sm">
                        <span
                            :class="[
                                'inline-flex items-center gap-1 text-xs font-medium',
                                isPositiveChange ? 'text-emerald-600' : 'text-rose-600',
                            ]"
                        >
                            <component
                                :is="isPositiveChange ? IconTrendingUp : IconTrendingDown"
                                class="h-4 w-4"
                                :stroke-width="2"
                            />
                            {{ isPositiveChange ? '+' : '' }}{{ change.value }}%
                        </span>
                        <span class="text-muted-foreground text-xs">{{ change.label }}</span>
                    </div>

                    <div v-if="footnote" class="mt-2 text-xs text-muted-foreground">{{ footnote }}</div>
                </div>

                <div v-if="hasChart" class="flex flex-col items-end">
                    <div
                        v-if="change"
                        :class="[
                            'flex items-center gap-1 text-xs font-medium',
                            isPositiveChange ? 'text-emerald-600' : 'text-rose-600',
                        ]"
                    >
                        <component
                            :is="isPositiveChange ? IconTrendingUp : IconTrendingDown"
                            class="h-3.5 w-3.5"
                            :stroke-width="2"
                        />
                        <span>{{ isPositiveChange ? '+' : '' }}{{ change.value }}%</span>
                    </div>
                    <component
                        v-if="sparkline && ApexChart"
                        :is="ApexChart"
                        :options="sparklineOptions"
                        :series="sparklineSeries"
                        height="40"
                        width="110"
                    />
                    <component v-else-if="chart" :is="chart" class="h-10 w-[110px]" />
                </div>
            </div>
        </template>

        <!-- Time variant -->
        <template v-else-if="variant === 'time'">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-sm font-medium text-muted-foreground">
                        <span>{{ title }}</span>
                        <span v-if="titleSuffix" class="text-muted-foreground/70"> {{ titleSuffix }}</span>
                    </div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight">{{ value }}</div>

                    <div v-if="change" class="mt-3 flex items-center gap-2 text-sm">
                        <span
                            :class="[
                                'inline-flex items-center gap-1 text-xs font-medium',
                                isPositiveChange ? 'text-emerald-600' : 'text-rose-600',
                            ]"
                        >
                            <component
                                :is="isPositiveChange ? IconTrendingUp : IconTrendingDown"
                                class="h-4 w-4"
                                :stroke-width="2"
                            />
                            {{ isPositiveChange ? '+' : '' }}{{ change.value }}%
                        </span>
                        <span class="text-muted-foreground">{{ change.label }}</span>
                    </div>
                </div>

                <div v-if="hasChart" class="flex flex-col items-end gap-1">
                    <div
                        v-if="change"
                        :class="[
                            'flex items-center gap-1 text-xs font-medium',
                            isPositiveChange ? 'text-emerald-600' : 'text-rose-600',
                        ]"
                    >
                        <component
                            :is="isPositiveChange ? IconTrendingUp : IconTrendingDown"
                            class="h-3.5 w-3.5"
                            :stroke-width="2"
                        />
                        <span>{{ isPositiveChange ? '+' : '' }}{{ change.value }}%</span>
                    </div>
                    <component
                        v-if="sparkline && ApexChart"
                        :is="ApexChart"
                        :options="sparklineOptions"
                        :series="sparklineSeries"
                        height="48"
                        width="140"
                    />
                    <component v-else-if="chart" :is="chart" class="h-12 w-[140px]" />
                </div>
            </div>
        </template>

        <!-- Company variant -->
        <template v-else-if="variant === 'company'">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-3">
                    <div
                        v-if="icon"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted"
                    >
                        <component :is="icon" class="h-5 w-5" />
                    </div>

                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold">{{ title }}</div>
                        <div v-if="description" class="truncate text-xs text-muted-foreground">{{ description }}</div>

                        <div class="mt-3 flex items-baseline gap-2">
                            <div class="text-2xl font-semibold tracking-tight">{{ value }}</div>
                            <div v-if="change" class="text-sm">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 font-medium',
                                        isPositiveChange ? 'text-emerald-600' : 'text-rose-600',
                                    ]"
                                >
                                    <component
                                        :is="isPositiveChange ? IconTrendingUp : IconTrendingDown"
                                        class="h-4 w-4"
                                        :stroke-width="2"
                                    />
                                    {{ isPositiveChange ? '+' : '' }}{{ change.value }}%
                                </span>
                            </div>
                        </div>

                        <div v-if="footnote" class="mt-1 text-xs text-muted-foreground">{{ footnote }}</div>
                    </div>
                </div>

                <div v-if="engagement" class="flex shrink-0 flex-col items-end">
                    <div class="flex items-center gap-2">
                        <div class="relative h-9 w-9">
                            <svg class="h-9 w-9 -rotate-90" viewBox="0 0 36 36">
                                <path
                                    class="text-muted-foreground/20"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                />
                                <path
                                    class="text-primary"
                                    :stroke-dasharray="`${engagement.value}, 100`"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                />
                            </svg>
                        </div>
                        <div class="text-sm font-semibold">{{ engagement.value }}</div>
                    </div>
                    <span class="mt-1 text-xs text-muted-foreground">{{ engagement.label }}</span>
                </div>
            </div>
        </template>
    </div>
</template>
