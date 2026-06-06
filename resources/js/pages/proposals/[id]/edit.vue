<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import ProposalBuilder from '@/pages/proposals/components/builder/ProposalBuilder.vue';
import { dashboard } from '@/routes';
import type { Proposal } from '@/types/models/proposal';

const props = defineProps<{
  proposal: Proposal;
}>();

const breadcrumbs = computed(() => [
  { title: 'dashboard', href: dashboard() },
  { title: 'proposals', href: '/proposals' },
  { title: props.proposal.title },
]);

const applyLayout = () => {
  setLayoutProps({
    title: 'edit proposal',
    description: 'Update blocks, pricing, and metadata.',
    breadcrumbs: breadcrumbs.value,
  });
};

applyLayout();

watch(
  () => props.proposal.title,
  () => applyLayout()
);
</script>

<template>
  <Head :title="`Edit · ${props.proposal.title}`" />
  <div class="-mx-4 -mb-4 h-[calc(100vh-64px)]">
    <ProposalBuilder mode="edit" :initial-proposal="props.proposal" />
  </div>
</template>
