<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    KpiStrip,
    RevenueChart,
    ProposalFunnel,
    ArAging,
    ProjectDelivery,
    TopAccounts,
    RecentActivity,
    UpcomingDeadlines,
} from '@/components/dashboard-widgets';
import { dashboard } from '@/routes';
import type {
    DashboardActivity,
    DashboardDeadline,
    RevenueChartData,
    KpiData,
    ProposalFunnelItem,
    ArAgingBucket,
    ActiveProject,
    TopAccount,
} from '@/types';

const page = usePage();

const kpiData = computed(() => page.props.kpiData as KpiData);
const proposalFunnel = computed(
    () => page.props.proposalFunnel as ProposalFunnelItem[],
);
const arAging = computed(() => page.props.arAging as ArAgingBucket[]);
const activeProjects = computed(
    () => page.props.activeProjects as ActiveProject[],
);
const topAccounts = computed(() => page.props.topAccounts as TopAccount[]);
const recentActivity = computed(
    () => page.props.recentActivity as DashboardActivity[],
);
const upcomingDeadlines = computed(
    () => page.props.upcomingDeadlines as DashboardDeadline[],
);
const revenueChart = computed(
    () => page.props.revenueChart as RevenueChartData,
);

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
        <KpiStrip :kpi-data="kpiData" />
        <RevenueChart :revenue-chart="revenueChart" />

        <div class="grid gap-4 md:grid-cols-2">
            <ProposalFunnel :funnel="proposalFunnel" />
            <ArAging :buckets="arAging" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <ProjectDelivery :projects="activeProjects" />
            <TopAccounts :accounts="topAccounts" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <RecentActivity :activities="recentActivity" />
            <UpcomingDeadlines :deadlines="upcomingDeadlines" />
        </div>
    </div>
</template>
