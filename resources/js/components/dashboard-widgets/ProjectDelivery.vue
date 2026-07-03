<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import type { ActiveProject } from '@/types';

const props = defineProps<{ projects: ActiveProject[] }>();

const projectChartOptions = computed(() => ({
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
    dataLabels: {
        enabled: true,
        formatter: (val: number) => `${val}%`,
        style: { fontSize: '11px', fontFamily: 'inherit' },
    },
    xaxis: {
        categories: props.projects.map((p) => p.name),
        max: 100,
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: { fontSize: '11px', fontFamily: 'inherit' },
            maxWidth: 160,
        },
    },
    colors: props.projects.map((p) => p.color ?? '#6366f1'),
    tooltip: {
        x: { show: false },
        y: {
            formatter: (val: number) => `${val}% complete`,
        },
    },
    grid: {
        borderColor: 'rgba(0,0,0,0.05)',
        xaxis: { lines: { show: false } },
    },
}));

const projectChartSeries = computed(() => [
    {
        name: 'Completion',
        data: props.projects.map((p) => p.completion),
    },
]);
</script>

<template>
    <div class="rounded-sm border border-border bg-background p-5">
        <h3 class="mb-4 text-lg font-semibold">Project Delivery</h3>
        <VueApexCharts
            type="bar"
            height="320"
            :options="projectChartOptions"
            :series="projectChartSeries"
        />
    </div>
</template>
