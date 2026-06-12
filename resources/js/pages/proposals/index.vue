<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Tag } from '@lucide/vue';
import { ref } from 'vue';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { dashboard } from '@/routes';
import type { Proposal } from '@/types/models/proposal';
import ProposalStatusFormDialog from '../proposal-status/dialogs/ProposalStatusFormDialog.vue';
import { createColumns } from './datatable/columns';
import DataTable from './datatable/data-table.vue';
import ProposalFormDialog from './dialogs/ProposalFormDialog.vue';

const props = defineProps<{
    proposals: Proposal[];
    filters?: {
        status?: string;
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingProposal = ref<Proposal | null>(null);
const statusDialogOpen = ref(false);
const editingStatus = ref<any>(null);

const columns = createColumns(
    (proposal) => {
        editingProposal.value = proposal;
        dialogOpen.value = true;
    },
    (proposal) => {
        router.delete(ProposalController.destroy(proposal.id).url);
    },
);

const statusTabs = [
    { value: 'all', label: 'All' },
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'accepted', label: 'Accepted' },
    { value: 'rejected', label: 'Rejected' },
];

const handleCreate = () => {
    editingProposal.value = null;
    dialogOpen.value = true;
};

const handleCreateStatus = () => {
    editingStatus.value = null;
    statusDialogOpen.value = true;
};

const handleViewStatuses = () => {
    router.visit('/proposal-status');
};

const handleSuccess = () => {
    dialogOpen.value = false;
    editingProposal.value = null;
    router.reload();
};

const handleStatusSuccess = () => {
    statusDialogOpen.value = false;
    editingStatus.value = null;
    router.reload({
        only: ['proposal_statuses'],
    });
};

function updateFilters(newFilters: Record<string, string | undefined>) {
    router.get(ProposalController.index().url, newFilters, {
        preserveState: true,
        replace: true,
    });
}

defineOptions({
    layout: {
        title: 'Proposals',
        description: 'Manage your proposals and track their status',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Proposals',
            },
        ],
    },
});
</script>

<template>
    <Head title="Proposals" />

    <div class="space-y-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold tracking-tight">Proposals</h4>
                <p class="text-muted-foreground">
                    Manage your proposals and track their status throughout the sales pipeline.
                </p>
            </div>
            <div class="flex gap-2">
                <Button type="button" @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    New Proposal
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">
                            <Tag class="mr-2 h-4 w-4" />
                            Status
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="handleCreateStatus">
                            <Plus class="mr-2 h-4 w-4" />
                            New Status
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleViewStatuses">
                            <Tag class="mr-2 h-4 w-4" />
                            View Statuses
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <div class="flex gap-2 border-b">
            <button
                v-for="tab in statusTabs"
                :key="tab.value"
                @click="updateFilters({ status: tab.value === 'all' ? undefined : tab.value, search: props.filters?.search })"
                :class="[
                    'px-4 py-2 text-sm font-medium transition-colors border-b-2 -mb-px',
                    props.filters?.status === tab.value
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground'
                ]"
            >
                {{ tab.label }}
            </button>
        </div>

        <DataTable
            :columns="columns"
            :data="proposals"
            :search-value="props.filters?.search"
            :on-search-update="(value: string | number) => updateFilters({ status: props.filters?.status, search: String(value) })"
        />

        <ProposalFormDialog
            v-model:open="dialogOpen"
            :proposal="editingProposal"
            @success="handleSuccess"
        />

        <ProposalStatusFormDialog
            v-model:open="statusDialogOpen"
            :status="editingStatus"
            @success="handleStatusSuccess"
        />
    </div>
</template>
