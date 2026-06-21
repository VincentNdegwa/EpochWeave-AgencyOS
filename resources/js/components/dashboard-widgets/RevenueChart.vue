<script setup lang="ts">
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useCurrency } from '@/composables/useCurrency';
import type { RevenueChartData } from '@/types';

const props = defineProps<{ revenueChart: RevenueChartData }>();

const { format: formatCurrency } = useCurrency();

const revenueRange = ref<'6m' | '12m'>('6m');

const sliceCount = computed(() => (revenueRange.value === '6m' ? 6 : 12));

const slicedLabels = computed(() =>
    props.revenueChart.labels.slice(-sliceCount.value),
);
const slicedRevenue = computed(() =>
    props.revenueChart.revenue.slice(-sliceCount.value),
);
const slicedPipeline = computed(() =>
    props.revenueChart.pipeline.slice(-sliceCount.value),
);

const revenueChartOptions = computed(() => ({
    chart: {
        type: 'bar' as const,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    plotOptions: {
        bar: {
            borderRadius: 4,
            columnWidth: '45%',
        },
    },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: ['transparent'] },
    xaxis: {
        categories: slicedLabels.value,
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => formatCurrency(val),
        },
    },
    colors: ['#3b82f6', '#10b981'],
    legend: {
        position: 'top' as const,
        horizontalAlign: 'right' as const,
    },
    tooltip: {
        y: {
            formatter: (val: number) => formatCurrency(val),
        },
    },
    grid: {
        borderColor: 'rgba(0,0,0,0.05)',
    },
}));

const revenueChartSeries = computed(() => [
    { name: 'Revenue', data: slicedRevenue.value },
    { name: 'Pipeline', data: slicedPipeline.value },
]);
</script>

<template>
    <div class="border border-border rounded-sm bg-background p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Revenue vs Pipeline</h3>
            <div class="inline-flex border bg-background p-0.5">
                <button
                    :class="[
                        'rounded-sm px-2.5 py-1 text-xs font-medium transition-colors',
                        revenueRange === '6m'
                            ? 'bg-foreground text-background'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                    @click="revenueRange = '6m'"
                >
                    6mo
                </button>
                <button
                    :class="[
                        'rounded-sm px-2.5 py-1 text-xs font-medium transition-colors',
                        revenueRange === '12m'
                            ? 'bg-foreground text-background'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                    @click="revenueRange = '12m'"
                >
                    12mo
                </button>
            </div>
        </div>
        <VueApexCharts
            type="bar"
            height="360"
            :options="revenueChartOptions"
            :series="revenueChartSeries"
        />
    </div>
</template>
