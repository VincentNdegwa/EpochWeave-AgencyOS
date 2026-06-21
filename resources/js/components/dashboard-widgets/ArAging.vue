<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useCurrency } from '@/composables/useCurrency';
import type { ArAgingBucket } from '@/types';

const props = defineProps<{ buckets: ArAgingBucket[] }>();

const { format: formatCurrency } = useCurrency();

const arAgingOptions = computed(() => ({
    chart: {
        type: 'donut' as const,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    labels: props.buckets.map((b) => b.label),
    colors: props.buckets.map((b) => b.color),
    legend: {
        position: 'bottom' as const,
        fontFamily: 'inherit',
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: { show: false },
                    value: {
                        show: true,
                        fontSize: '18px',
                        fontWeight: 600,
                        color: '#1f2937',
                        formatter: (val: string) => formatCurrency(Number(val)),
                    },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '12px',
                        fontWeight: 400,
                        color: '#6b7280',
                        formatter: () =>
                            formatCurrency(
                                props.buckets.reduce((s, b) => s + b.amount, 0),
                            ),
                    },
                },
            },
        },
    },
    tooltip: {
        y: {
            formatter: (val: number) => formatCurrency(val),
        },
    },
    dataLabels: { enabled: false },
}));

const arAgingSeries = computed(() => props.buckets.map((b) => b.amount));
</script>

<template>
    <div class="border border-border rounded-sm bg-background p-5">
        <h3 class="mb-4 text-lg font-semibold">AR Aging</h3>
        <VueApexCharts
            type="donut"
            height="280"
            :options="arAgingOptions"
            :series="arAgingSeries"
        />
        <div class="mt-3 grid grid-cols-2 gap-2">
            <div
                v-for="bucket in props.buckets"
                :key="bucket.label"
                class="flex items-center gap-2 text-xs"
            >
                <span
                    class="h-2.5 w-2.5 rounded-full"
                    :style="{ backgroundColor: bucket.color }"
                />
                <span class="text-muted-foreground">{{ bucket.label }}:</span>
                <span class="font-medium">{{ formatCurrency(bucket.amount) }}</span>
            </div>
        </div>
    </div>
</template>
