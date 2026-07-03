<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import type { ProposalFunnelItem } from '@/types';

const props = defineProps<{ funnel: ProposalFunnelItem[] }>();

const funnelChartOptions = computed(() => ({
    chart: {
        type: 'bar' as const,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    plotOptions: {
        bar: {
            borderRadius: 4,
            horizontal: true,
            barHeight: '50%',
        },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.funnel.map((f) => f.stage),
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: { fontSize: '12px', fontFamily: 'inherit' },
        },
    },
    colors: props.funnel.map((f) => f.color),
    tooltip: {
        y: {
            formatter: (val: number) => String(val),
        },
    },
    grid: {
        borderColor: 'rgba(0,0,0,0.05)',
        xaxis: { lines: { show: false } },
    },
}));

const funnelChartSeries = computed(() => [
    {
        name: 'Count',
        data: props.funnel.map((f) => f.count),
    },
]);
</script>

<template>
    <div class="rounded-sm border border-border bg-background p-5">
        <h3 class="mb-4 text-lg font-semibold">Proposal Funnel</h3>
        <VueApexCharts
            type="bar"
            height="280"
            :options="funnelChartOptions"
            :series="funnelChartSeries"
        />
    </div>
</template>
