<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BarChart3 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    year: number;
    revenueByMonth: number[];
    proposalStatusCounts: { status: string; count: number; color: string }[];
    topClients: { name: string; total: number }[];
    hoursByMonth: number[];
}>();

const monthLabels = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
];

const revenueTotal = computed(() =>
    props.revenueByMonth.reduce((a, b) => a + b, 0),
);
const hoursTotal = computed(() =>
    props.hoursByMonth.reduce((a, b) => a + b, 0),
);

const changeYear = (delta: number) => {
    router.get('/reports', { year: props.year + delta });
};
</script>

<template>
    <Head title="Reports" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <BarChart3 class="h-5 w-5 text-muted-foreground" />
                <h1 class="text-lg font-semibold">Reports</h1>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="changeYear(-1)">{{
                    year - 1
                }}</Button>
                <span class="text-sm font-medium">{{ year }}</span>
                <Button variant="outline" size="sm" @click="changeYear(1)">{{
                    year + 1
                }}</Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border bg-card p-4">
                <p class="text-xs text-muted-foreground">Revenue</p>
                <p class="text-2xl font-bold">
                    {{
                        revenueTotal.toLocaleString(undefined, {
                            style: 'currency',
                            currency: 'USD',
                        })
                    }}
                </p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="text-xs text-muted-foreground">Hours Tracked</p>
                <p class="text-2xl font-bold">{{ hoursTotal }}</p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="text-xs text-muted-foreground">Proposals</p>
                <p class="text-2xl font-bold">
                    {{ proposalStatusCounts.reduce((a, b) => a + b.count, 0) }}
                </p>
            </div>
            <div class="rounded-lg border bg-card p-4">
                <p class="text-xs text-muted-foreground">Top Client</p>
                <p class="truncate text-lg font-bold">
                    {{ topClients[0]?.name ?? '—' }}
                </p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-lg border bg-card p-4">
                <h2 class="mb-4 text-sm font-semibold">Revenue by Month</h2>
                <div class="flex h-48 items-end gap-1">
                    <div
                        v-for="(value, index) in revenueByMonth"
                        :key="index"
                        class="flex flex-1 flex-col items-center gap-1"
                    >
                        <div
                            class="w-full rounded bg-primary/80 transition-all"
                            :style="{
                                height: `${(value / (Math.max(...revenueByMonth) || 1)) * 100}%`,
                            }"
                        />
                        <span class="text-[10px] text-muted-foreground">{{
                            monthLabels[index]
                        }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border bg-card p-4">
                <h2 class="mb-4 text-sm font-semibold">Proposal Status</h2>
                <div class="space-y-2">
                    <div
                        v-for="item in proposalStatusCounts"
                        :key="item.status"
                        class="flex items-center justify-between"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="h-2.5 w-2.5 rounded-full"
                                :style="{ backgroundColor: item.color }"
                            />
                            <span class="text-sm">{{ item.status }}</span>
                        </div>
                        <span class="text-sm font-medium">{{
                            item.count
                        }}</span>
                    </div>
                    <p
                        v-if="!proposalStatusCounts.length"
                        class="text-sm text-muted-foreground"
                    >
                        No proposals yet.
                    </p>
                </div>
            </div>

            <div class="rounded-lg border bg-card p-4">
                <h2 class="mb-4 text-sm font-semibold">Top Clients</h2>
                <div class="space-y-2">
                    <div
                        v-for="client in topClients"
                        :key="client.name"
                        class="flex items-center justify-between"
                    >
                        <span class="text-sm">{{ client.name }}</span>
                        <span class="text-sm font-medium">{{
                            client.total.toLocaleString(undefined, {
                                style: 'currency',
                                currency: 'USD',
                            })
                        }}</span>
                    </div>
                    <p
                        v-if="!topClients.length"
                        class="text-sm text-muted-foreground"
                    >
                        No invoices yet.
                    </p>
                </div>
            </div>

            <div class="rounded-lg border bg-card p-4">
                <h2 class="mb-4 text-sm font-semibold">Hours by Month</h2>
                <div class="flex h-48 items-end gap-1">
                    <div
                        v-for="(value, index) in hoursByMonth"
                        :key="index"
                        class="flex flex-1 flex-col items-center gap-1"
                    >
                        <div
                            class="w-full rounded bg-emerald-500/80 transition-all"
                            :style="{
                                height: `${(value / (Math.max(...hoursByMonth) || 1)) * 100}%`,
                            }"
                        />
                        <span class="text-[10px] text-muted-foreground">{{
                            monthLabels[index]
                        }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
