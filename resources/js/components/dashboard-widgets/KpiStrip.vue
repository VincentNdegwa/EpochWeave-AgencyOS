<script setup lang="ts">
import {
    ReceiptText,
    TrendingDown,
    TrendingUp,
    Briefcase,
    DollarSign,
    Target,
    Zap,
} from '@lucide/vue';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useCurrency } from '@/composables/useCurrency';
import type { KpiData } from '@/types';

const props = defineProps<{ kpiData: KpiData }>();

const { format: formatCurrency } = useCurrency();

interface KpiCardDef {
    key: keyof KpiData;
    label: string;
    icon: unknown;
    format: (v: number) => string;
    invertChange?: boolean;
}

const kpiDefs: KpiCardDef[] = [
    {
        key: 'revenue',
        label: 'Revenue MTD',
        icon: DollarSign,
        format: (v) => formatCurrency(v),
    },
    {
        key: 'outstanding',
        label: 'Outstanding',
        icon: ReceiptText,
        format: (v) => String(v),
        invertChange: true,
    },
    {
        key: 'pipeline',
        label: 'Pipeline Value',
        icon: Target,
        format: (v) => formatCurrency(v),
    },
    {
        key: 'win_rate',
        label: 'Win Rate',
        icon: Zap,
        format: (v) => `${v}%`,
    },
    {
        key: 'active_projects',
        label: 'Active Projects',
        icon: Briefcase,
        format: (v) => String(v),
    },
    {
        key: 'avg_deal_size',
        label: 'Avg Deal Size',
        icon: TrendingUp,
        format: (v) => formatCurrency(v),
    },
];

const cards = computed(() =>
    kpiDefs.map((def) => {
        const item = props.kpiData[def.key];
        const change = item.change;
        const isPositive =
            change !== null &&
            ((change.value >= 0 && !def.invertChange) ||
                (change.value < 0 && def.invertChange));

        return {
            key: def.key,
            label: def.label,
            icon: def.icon,
            formattedValue: def.format(item.value),
            sparkline: item.sparkline,
            change,
            isPositive,
        };
    }),
);

function sparklineOptions(series: number[]) {
    return {
        chart: {
            type: 'area' as const,
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        stroke: { curve: 'smooth' as const, width: 2 },
        fill: { opacity: 0.15 },
        colors: [
            series[series.length - 1] >= series[0] ? '#22c55e' : '#ef4444',
        ],
        tooltip: { enabled: false },
        xaxis: { crosshairs: { show: false } },
        yaxis: { show: false },
        grid: { show: false },
        dataLabels: { enabled: false },
    };
}
</script>

<template>
    <div class="border-t border-b border-border bg-background">
        <div class="grid divide-x divide-border grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            <div
                v-for="card in cards"
                :key="card.key"
                class="px-6 py-4"
            >
                <div class="flex items-center gap-2">
                    <component
                        :is="card.icon"
                        class="h-4 w-4 text-muted-foreground"
                    />
                    <span
                        class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground"
                    >
                        {{ card.label }}
                    </span>
                </div>

                <div class="mt-0.5 flex items-baseline gap-1.5">
                    <span class="text-base font-bold text-foreground">
                        {{ card.formattedValue }}
                    </span>
                    <span
                        v-if="card.change"
                        :class="[
                            'inline-flex items-center gap-0.5 text-xs font-medium',
                            card.isPositive ? 'text-emerald-600' : 'text-rose-600',
                        ]"
                    >
                        <component
                            :is="card.isPositive ? TrendingUp : TrendingDown"
                            class="h-3.5 w-3.5"
                            :stroke-width="2"
                        />
                        {{ card.change.value >= 0 ? '+' : '' }}
                        {{ card.change.value }}%
                    </span>
                </div>

                <div class="mt-2 h-6">
                    <VueApexCharts
                        type="area"
                        height="24"
                        :options="sparklineOptions(card.sparkline)"
                        :series="[{ data: card.sparkline }]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
