<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Tag, List, Kanban } from '@lucide/vue';
import { ref, computed } from 'vue';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
import { Button } from '@/components/ui/button';
import {
    ButtonGroup,
} from '@/components/ui/button-group';
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
import KanbanView from './KanbanView.vue';

const { proposals, proposal_statuses, display_mode, movement_rules, filters } = defineProps<{
    proposals: Proposal[];
    proposal_statuses: any[];
    display_mode: string;
    movement_rules: Record<string, number[]>;
    filters?: {
        status?: string;
        search?: string;
    };
}>();

const dialogOpen = ref(false);
const editingProposal = ref<Proposal | null>(null);
const statusDialogOpen = ref(false);
const editingStatus = ref<any>(null);

const columns = createColumns();

const statusTabs = computed(() => [
    { value: 'all', label: 'All' },
    ...proposal_statuses.map((status: any) => ({
        value: String(status.id),
        label: status.title
    }))
]);

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
                    Manage your proposals and track their status throughout the
                    sales pipeline.
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
            :data="proposals"
            :filters="filters"
            :on-filter="updateFilters"
        />

        <KanbanView
            v-else
            :proposals="proposals"
            :proposal_statuses="proposal_statuses"
            :movement_rules="movement_rules"
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
