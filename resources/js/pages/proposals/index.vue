<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { Proposal } from '@/types/models/proposal';
import DataTable from '@/pages/proposals/datatable/data-table.vue';
import { createColumns } from '@/pages/proposals/datatable/columns';

const props = defineProps<{
  proposals: Proposal[];
}>();

const columns = createColumns(
  (proposal) => router.visit(ProposalController.edit(proposal.id).url),
  (proposal) => router.delete(ProposalController.destroy(proposal.id).url)
);

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
</template>
