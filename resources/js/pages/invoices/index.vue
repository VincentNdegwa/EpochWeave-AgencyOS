<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, List, Kanban } from '@lucide/vue';
import { computed } from 'vue';
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import { Button } from '@/components/ui/button';
import {
    ButtonGroup,
} from '@/components/ui/button-group';
import { useInvoiceStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import type { Invoice } from '@/types/models/invoice';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import KanbanView from './KanbanView.vue';

const { invoices, display_mode, filters } = defineProps<{
    invoices: Invoice[];
    display_mode: string;
    filters?: {
        status?: string;
        search?: string;
    };
}>();

const invoiceStatuses = useInvoiceStatuses();

const columns = createColumns();

const statusTabs = computed(() => [
    { value: 'all', label: 'All' },
    ...invoiceStatuses.values.map((s) => ({ value: s.value, label: s.label })),
]);

const handleCreate = () => {
    router.visit(InvoiceController.create().url)
};

const handleDisplayModeChange = (mode: string) => {
    router.post('/user-preferences/display-mode', {
        display_mode: mode,
    }, {
        preserveState: true,
        onSuccess: () => {
            router.reload({
                only: ['display_mode'],
            });
        },
    });
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(InvoiceController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Invoices',
        description: 'Manage your invoices and track payment status',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Invoices',
            },
        ],
    },
});
</script>

<template>
    <Head title="Invoices" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Invoices</h4>
                <p class="text-muted-foreground">
                    Manage your invoices and track payment status throughout the billing lifecycle.
                </p>
            </div>
            <div class="flex gap-2">
                <Button type="button" @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    New Invoice
                </Button>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex gap-2 border-b" v-if="display_mode === 'list'">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.value"
                    @click="
                        updateFilters({
                            status: tab.value === 'all' ? undefined : tab.value,
                            search: filters?.search,
                        })
                    "
                    :class="[
                        '-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                        filters?.status === tab.value
                            ? 'border-primary text-foreground'
                            : 'border-transparent text-muted-foreground hover:text-foreground',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
            <ButtonGroup class="ml-auto">
                <Button
                    :variant="display_mode === 'list' ? 'default' : 'ghost'"
                    size="sm"
                    @click="handleDisplayModeChange('list')"
                    class="h-7 px-2"
                >
                    <List class="h-3.5 w-3.5" />
                </Button>
                <Button
                    :variant="display_mode === 'kanban' ? 'default' : 'ghost'"
                    size="sm"
                    @click="handleDisplayModeChange('kanban')"
                    class="h-7 px-2"
                >
                    <Kanban class="h-3.5 w-3.5" />
                </Button>
            </ButtonGroup>
        </div>

        <DataTable
            v-if="display_mode === 'list'"
            :columns="columns"
            :data="invoices"
            :search-value="filters?.search"
            :on-search-update="
                (value: string | number) =>
                    updateFilters({
                        status: filters?.status,
                        search: String(value),
                    })
            "
        />

        <KanbanView
            v-else
            :invoices="invoices"
        />
    </div>
</template>
