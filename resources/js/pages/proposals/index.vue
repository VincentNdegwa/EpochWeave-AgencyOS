<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
import { Button } from '@/components/ui/button';
import ConfirmationDialog from '@/components/ui/confirmation-dialog/ConfirmationDialog.vue';
import { dashboard } from '@/routes';
import type { Proposal } from '@/types/models/proposal';
import DataTable from '@/pages/proposals/datatable/data-table.vue';
import { createColumns } from '@/pages/proposals/datatable/columns';

const props = defineProps<{
  proposals: Proposal[];
}>();

const deleteDialog = ref({
  open: false,
  proposal: null as Proposal | null,
});

const handleDelete = (proposal: Proposal) => {
  deleteDialog.value = {
    open: true,
    proposal,
  };
};

const confirmDelete = () => {
  if (deleteDialog.value.proposal) {
    router.delete(ProposalController.destroy(deleteDialog.value.proposal.id).url);
  }
  deleteDialog.value.open = false;
};

const cancelDelete = () => {
  deleteDialog.value.open = false;
};

const columns = createColumns(handleDelete);

defineOptions({
  layout: {
    title: 'proposals',
    description: 'Track proposal drafts, sent packets, and signatures.',
    breadcrumbs: [
      { title: 'dashboard', href: dashboard() },
      { title: 'proposals' },
    ],
  },
});
</script>

<template>
  <Head title="Proposals" />
  <div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Proposals</h1>
        <p class="text-muted-foreground">Monitor pipeline health and jump back into your builder.</p>
      </div>
      <div class="flex gap-2">
        <Link :href="ProposalController.create().url">
          <Button>
            <Plus class="mr-2 h-4 w-4" />
            New proposal
          </Button>
        </Link>
      </div>
    </div>

    <DataTable :columns="columns" :data="props.proposals" />
  </div>

  <ConfirmationDialog
    :open="deleteDialog.open"
    title="Delete Proposal"
    :description="`Are you sure you want to delete '${deleteDialog.proposal?.title}'? This action cannot be undone.`"
    confirmText="Delete"
    cancelText="Cancel"
    variant="destructive"
    @confirm="confirmDelete"
    @cancel="cancelDelete"
  />
</template>
