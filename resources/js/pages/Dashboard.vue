<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Clock,
    FileText,
    ReceiptText,
    AlertTriangle,
    CheckCircle2,
    TrendingUp,
    Calendar,
} from '@lucide/vue';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { dashboard } from '@/routes';
import { useCurrency } from '@/composables/useCurrency';
import type { DashboardStats, DashboardActivity, DashboardDeadline, RevenueChartData } from '@/types';

const page = usePage();

const stats = computed(() => page.props.stats as DashboardStats);
const recentActivity = computed(() => page.props.recentActivity as DashboardActivity[]);
const upcomingDeadlines = computed(() => page.props.upcomingDeadlines as DashboardDeadline[]);
const revenueChart = computed(() => page.props.revenueChart as RevenueChartData);

const { format: formatCurrency } = useCurrency();

const chartOptions = computed(() => ({
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
        categories: revenueChart.value.labels,
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

const chartSeries = computed(() => [
    { name: 'Revenue', data: revenueChart.value.revenue },
    { name: 'Pipeline', data: revenueChart.value.pipeline },
]);

const statCards = computed(() => [
    {
        label: 'Open Proposals',
        value: stats.value.open_proposals,
        icon: FileText,
        color: 'text-blue-600',
        bg: 'bg-blue-50',
    },
    {
        label: 'Outstanding Invoices',
        value: stats.value.outstanding_invoices,
        icon: ReceiptText,
        color: 'text-amber-600',
        bg: 'bg-amber-50',
    },
    {
        label: 'Overdue Invoices',
        value: stats.value.overdue_invoices,
        icon: AlertTriangle,
        color: 'text-red-600',
        bg: 'bg-red-50',
    },
    {
        label: 'Open Tasks',
        value: stats.value.open_tasks,
        icon: CheckCircle2,
        color: 'text-emerald-600',
        bg: 'bg-emerald-50',
    },
    {
        label: 'Monthly Revenue',
        value: formatCurrency(stats.value.monthly_revenue),
        icon: TrendingUp,
        color: 'text-violet-600',
        bg: 'bg-violet-50',
    },
    {
        label: 'Unbilled Hours',
        value: `${stats.value.unbilled_hours}h`,
        icon: Clock,
        color: 'text-orange-600',
        bg: 'bg-orange-50',
    },
]);

const activityIcon = (type: string) => {
    switch (type) {
        case 'proposal_signed': return FileText;
        case 'invoice_sent': return ReceiptText;
        case 'task_completed': return CheckCircle2;
        default: return BarChart3;
    }
};

const activityColor = (type: string) => {
    switch (type) {
        case 'proposal_signed': return 'text-emerald-600 bg-emerald-50';
        case 'invoice_sent': return 'text-blue-600 bg-blue-50';
        case 'task_completed': return 'text-violet-600 bg-violet-50';
        default: return 'text-gray-600 bg-gray-50';
    }
};

const deadlineIcon = (type: string) => {
    switch (type) {
        case 'proposal_expiring': return FileText;
        case 'invoice_due': return ReceiptText;
        case 'task_due': return CheckCircle2;
        default: return Calendar;
    }
};

const deadlineColor = (type: string) => {
    switch (type) {
        case 'proposal_expiring': return 'text-amber-600 bg-amber-50';
        case 'invoice_due': return 'text-red-600 bg-red-50';
        case 'task_due': return 'text-blue-600 bg-blue-50';
        default: return 'text-gray-600 bg-gray-50';
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="rounded-xl border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <div :class="['flex h-10 w-10 items-center justify-center rounded-lg', card.bg]">
                        <component :is="card.icon" :class="['h-5 w-5', card.color]" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ card.label }}</p>
                        <p class="text-xl font-bold text-foreground">{{ card.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl border bg-card p-5 shadow-sm lg:col-span-2">
                <h3 class="text-sm font-semibold text-foreground">Revenue vs Pipeline</h3>
                <VueApexCharts
                    type="bar"
                    height="320"
                    :options="chartOptions"
                    :series="chartSeries"
                />
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-foreground">Upcoming Deadlines</h3>
                <div class="mt-4 space-y-3">
                    <div
                        v-for="item in upcomingDeadlines"
                        :key="`${item.type}-${item.title}`"
                        class="flex items-start gap-3"
                    >
                        <div :class="['flex h-8 w-8 shrink-0 items-center justify-center rounded-md', deadlineColor(item.type)]">
                            <component :is="deadlineIcon(item.type)" class="h-4 w-4" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-foreground">{{ item.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ item.date }}
                                <span v-if="item.account">&middot; {{ item.account }}</span>
                                <span v-if="item.project">&middot; {{ item.project }}</span>
                            </p>
                        </div>
                    </div>
                    <p v-if="!upcomingDeadlines.length" class="text-sm text-muted-foreground">No upcoming deadlines.</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-foreground">Recent Activity</h3>
            <div class="mt-4 space-y-3">
                <div
                    v-for="item in recentActivity"
                    :key="`${item.type}-${item.title}-${item.date}`"
                    class="flex items-start gap-3"
                >
                    <div :class="['flex h-8 w-8 shrink-0 items-center justify-center rounded-md', activityColor(item.type)]">
                        <component :is="activityIcon(item.type)" class="h-4 w-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">{{ item.title }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ item.type.replace('_', ' ') }}
                            <span v-if="item.amount">&middot; {{ formatCurrency(item.amount) }}</span>
                            <span v-if="item.account">&middot; {{ item.account }}</span>
                            <span v-if="item.project">&middot; {{ item.project }}</span>
                        </p>
                    </div>
                </div>
                <p v-if="!recentActivity.length" class="text-sm text-muted-foreground">No recent activity.</p>
            </div>
        </div>
    </div>
</template>
