<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { create as accountCreate } from '@/actions/App/Http/Controllers/AccountController';
import AccountController from '@/actions/App/Http/Controllers/AccountController';
import { Button } from '@/components/ui/button';
import { useCurrency } from '@/composables/useCurrency';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import type { Account } from '@/types/models/account';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';

defineProps<{
    accounts: Account[];
}>();

const { all: accountStatuses } = useAccountStatuses();
const { format: formatCurrency } = useCurrency();
const columns = createColumns(
    accountStatuses,
    (account) => {
        router.delete(AccountController.destroy(account.id).url);
    },
    formatCurrency,
);

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

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Accounts</h1>
                <p class="text-muted-foreground">
                    Manage your client accounts and their contacts.
                </p>
            </div>
            <Link :href="accountCreate().url">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Account
                </Button>
            </Link>
        </div>

        <!-- Data Table -->
        <DataTable :columns="columns" :data="accounts" />
    </div>
</template>
