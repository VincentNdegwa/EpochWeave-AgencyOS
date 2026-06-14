<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import AccountController from '@/actions/App/Http/Controllers/AccountController';
import { Button } from '@/components/ui/button';
import { StatsCard } from '@/components/ui/stats-card';
import { useCurrency } from '@/composables/useCurrency';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import type { Account } from '@/types/models/account';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import AccountFormDialog from './dialogs/AccountFormDialog.vue';

defineProps<{
    accounts: Account[];
    stats: {
        total: { value: number; change?: { value: number; label: string } };
        lead: { value: number; change?: { value: number; label: string } };
        opportunity: {
            value: number;
            change?: { value: number; label: string };
        };
        client: { value: number; change?: { value: number; label: string } };
        archived: { value: number; change?: { value: number; label: string } };
    };
    filters: {
        status: string;
        search?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const { all: accountStatuses } = useAccountStatuses();
const { format: formatCurrency } = useCurrency();
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingAccount = ref<Account | undefined>(undefined);
const columns = createColumns(
    accountStatuses,
    (account) => {
        router.delete(AccountController.destroy(account.id).url);
    },
    formatCurrency,
    (account) => {
        editingAccount.value = account;
        isEditDialogOpen.value = true;
    },
);

const statusTabs = [
    { value: 'all', label: 'All' },
    { value: 'lead', label: 'Leads' },
    { value: 'opportunity', label: 'Opportunities' },
    { value: 'client', label: 'Clients' },
    { value: 'archived', label: 'Archived' },
];

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(AccountController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Accounts',
        description: 'Manage your client accounts',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Accounts',
            },
        ],
    },
});
</script>

<template>
    <Head title="Accounts" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Accounts</h4>
                <p class="text-muted-foreground">
                    Manage your client accounts and their contacts.
                </p>
            </div>
            <Button type="button" @click="isCreateDialogOpen = true">
                <Plus class="mr-2 h-4 w-4" />
                New Account
            </Button>
        </div>

        <!-- Overview Cards -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <StatsCard
                title="Total Accounts"
                :value="stats.total.value"
                :change="stats.total.change"
            />
            <StatsCard
                title="Leads"
                :value="stats.lead.value"
                :change="stats.lead.change"
            />
            <StatsCard
                title="Opportunities"
                :value="stats.opportunity.value"
                :change="stats.opportunity.change"
            />
            <StatsCard
                title="Clients"
                :value="stats.client.value"
                :change="stats.client.change"
            />
            <StatsCard
                title="Archived"
                :value="stats.archived.value"
                :change="stats.archived.change"
            />
        </div>

        <!-- Status Tabs -->
        <div class="flex gap-2 border-b">
            <button
                v-for="tab in statusTabs"
                :key="tab.value"
                @click="
                    updateFilters({
                        status: tab.value === 'all' ? undefined : tab.value,
                        search: filters.search,
                        date_from: filters.date_from,
                        date_to: filters.date_to,
                    })
                "
                :class="[
                    '-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                    filters.status === tab.value
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground',
                ]"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Data Table -->
        <DataTable
            :columns="columns"
            :data="accounts"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <AccountFormDialog
            :open="isCreateDialogOpen"
            @update:open="isCreateDialogOpen = $event"
        />
        <AccountFormDialog
            :open="isEditDialogOpen"
            :account="editingAccount"
            @update:open="isEditDialogOpen = $event"
        />
    </div>
</template>
