<script setup lang="ts">
import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/vue3';
import {
    Building2,
    Send,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import ConfirmationDialog from '@/components/ui/confirmation-dialog/ConfirmationDialog.vue';
import { useCurrency } from '@/composables/useCurrency';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import { dashboard } from '@/routes';
import { show as accountShow } from '@/routes/accounts';
import proposals from '@/routes/proposals';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';
import type { Proposal } from '@/types/models/proposal';
import ProposalActions from './components/ProposalActions.vue';

const props = defineProps<{
    proposal: Proposal;
    activities: { id: number; type: string; description: string; created_at: string; user?: { id: number; name: string } | null }[];
}>();

const { format: formatCurrency } = useCurrency();
const builderStore = useProposalBuilderStore();
const workspaceStore = useWorkspaceStore();
const page = usePage();
const workspace = page.props.workspace ?? null;

onMounted(() => {
    workspaceStore.setWorkspace(workspace);
    builderStore.hydrateProposal(props.proposal, { mode: 'proposal' });
});

const formattedValue = computed(() =>
    formatCurrency(props.proposal.grand_total ?? 0, {
        currency: props.proposal.currency ?? 'USD',
    }),
);

const depositSummary = computed(() => {
    if (!props.proposal.requires_deposit) {
        return 'Not required';
    }

    if (props.proposal.deposit_type === 'percentage') {
        return `${props.proposal.deposit_value ?? 0}% upfront`;
    }

    return formatCurrency(
        props.proposal.deposit_amount ?? props.proposal.deposit_value ?? 0,
        {
            currency: props.proposal.currency ?? 'USD',
        },
    );
});

const proposalNumber = computed(
    () => props.proposal.proposal_number ?? props.proposal.token ?? 'DRAFT',
);

const validUntilDisplay = computed(() =>
    formatDate(props.proposal.valid_until),
);
const validityStatus = computed(() =>
    describeValidity(props.proposal.valid_until),
);

const accountName = computed(
    () => props.proposal.account?.company_name ?? 'Unassigned',
);
const accountHref = computed(() =>
    props.proposal.account?.id
        ? accountShow(props.proposal.account.id).url
        : null,
);

const activeTab = ref('overview');
const showSendDialog = ref(false);

const tabs = [
    { key: 'overview', label: 'Overview' },
    { key: 'preview', label: 'Preview' },
    { key: 'comments', label: 'Comments' },
    { key: 'activity', label: 'Activity' },
    { key: 'notes', label: 'Notes' },
];

const sendProposal = () => {
    router.post(proposals.send(props.proposal).url, {}, {
        onSuccess: () => {
            showSendDialog.value = false;
        },
    });
};


setLayoutProps({
    title: 'Proposal Preview',
    description: 'Review the live client experience and supporting details.',
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Proposals', href: '/proposals' },
        { title: props.proposal.title },
    ],
});

function formatDate(value?: string | null): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(
        new Date(value),
    );
}

function formatDateTime(value?: string | null): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function describeValidity(value?: string | null): string {
    if (!value) {
        return 'No expiry date';
    }

    const target = new Date(value).getTime();
    const diffDays = Math.ceil((target - Date.now()) / 86_400_000);

    if (diffDays < 0) {
        return 'Expired';
    }

    if (diffDays === 0) {
        return 'Expires today';
    }

    if (diffDays === 1) {
        return 'Expires tomorrow';
    }

    return `Expires in ${diffDays} days`;
}
</script>

<template>
    <Head :title="`Proposal · ${props.proposal.title}`" />

    <div class="flex h-[calc(100vh-64px)] flex-col">
        <div class="border-b border-border pb-4 bg-background/95 backdrop-blur">
            <div class="flex items-center justify-between">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl leading-snug font-semibold text-foreground">
                        {{ props.proposal.title }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            v-if="props.proposal.proposal_status"
                            :style="
                                props.proposal.proposal_status.color
                                    ? {
                                          backgroundColor:
                                              props.proposal.proposal_status.color,
                                          color: 'white',
                                      }
                                    : {}
                            "
                        >
                            {{ props.proposal.proposal_status.title }}
                        </Badge>
                        <Badge variant="outline" class="font-mono text-xs">
                            #{{ proposalNumber }}
                        </Badge>
                        <p class="text-xs text-muted-foreground">
                            Updated {{ formatDateTime(props.proposal.updated_at) }} •
                            Views {{ props.proposal.view_count ?? 0 }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button 
                        variant="outline" 
                        class="gap-2"
                        @click="showSendDialog = true"
                        :disabled="!props.proposal.account_contact"
                    >
                        <Send class="h-4 w-4" />
                        {{ props.proposal.sent_at ? 'Resend' : 'Send' }}
                    </Button>
                    <ProposalActions
                        :proposal="props.proposal"
                        variant="split"
                        size="sm"
                    />
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-border bg-background">
            <div class="flex">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                    :class="
                        activeTab === tab.key
                            ? 'border-primary text-primary'
                            : 'border-transparent text-muted-foreground hover:text-foreground'
                    "
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-hidden">
            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'" class="h-full overflow-y-auto p-6 space-y-6">
                    <!-- Financial Stats -->
                    <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-muted/50 rounded-lg p-4">
                            <p class="text-xs text-muted-foreground uppercase mb-1">Total Value</p>
                            <p class="text-2xl font-semibold">{{ formattedValue }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-4">
                            <p class="text-xs text-muted-foreground uppercase mb-1">Deposit</p>
                            <p class="text-sm">{{ depositSummary }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-4">
                            <p class="text-xs text-muted-foreground uppercase mb-1">Validity</p>
                            <p class="text-sm font-medium">{{ validUntilDisplay }}</p>
                            <p class="text-xs text-muted-foreground">{{ validityStatus }}</p>
                        </div>
                    </section>

                    <!-- Account & Contact -->
                    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold text-muted-foreground uppercase">Account</h3>
                            <div class="bg-muted/50 rounded-lg p-4">
                                <div class="flex items-center gap-2 text-sm font-medium">
                                    <Building2 class="h-4 w-4 text-muted-foreground" />
                                    <template v-if="accountHref">
                                        <Link
                                            :href="accountHref"
                                            class="text-primary underline-offset-4 hover:underline"
                                        >
                                            {{ accountName }}
                                        </Link>
                                    </template>
                                    <template v-else>
                                        {{ accountName }}
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold text-muted-foreground uppercase">Contact Person</h3>
                            <div v-if="props.proposal.account_contact" class="bg-muted/50 rounded-lg p-4">
                                <div class="flex items-start gap-2 text-sm">
                                    <div class="h-4 w-4 text-muted-foreground mt-0.5">👤</div>
                                    <div>
                                        <p class="font-medium">
                                            {{ `${props.proposal.account_contact.first_name} ${props.proposal.account_contact.last_name}` }}
                                        </p>
                                        <p class="text-muted-foreground text-xs">
                                            {{ props.proposal.account_contact.email }}
                                        </p>
                                        <p v-if="props.proposal.account_contact.job_title" class="text-muted-foreground text-xs">
                                            {{ props.proposal.account_contact.job_title }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="bg-muted/50 rounded-lg p-4 text-center text-muted-foreground text-sm">
                                No contact assigned
                            </div>
                        </div>
                    </section>

                    <!-- Activity -->
                    <section>
                        <h3 class="text-sm font-semibold text-muted-foreground uppercase mb-3">Activity</h3>
                        <ActivityTimeline :activities="props.activities" />
                    </section>

                    <!-- Team Member -->
                    <section>
                        <h3 class="text-sm font-semibold text-muted-foreground uppercase mb-3">Assigned Team Member</h3>
                        <div v-if="props.proposal.user" class="bg-muted/50 rounded-lg p-4">
                            <div class="flex items-start gap-2 text-sm">
                                <div class="h-4 w-4 text-muted-foreground mt-0.5">👨‍💼</div>
                                <div>
                                    <p class="font-medium">
                                        {{ props.proposal.user.name }}
                                    </p>
                                    <p class="text-muted-foreground text-xs">
                                        {{ props.proposal.user.email }}
                                    </p>
                                    <p class="text-muted-foreground text-xs">
                                        Assigned to proposal
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="bg-muted/50 rounded-lg p-4 text-center text-muted-foreground text-sm">
                            No team member assigned
                        </div>
                    </section>
                </div>

            <!-- Preview Tab -->
            <div v-else-if="activeTab === 'preview'" class="h-full">
                <ProposalCanvas :is-locked="true" :builder-mode="'proposal'" />
            </div>

            <!-- Coming Soon Tabs -->
            <div v-else class="h-full flex items-center justify-center">
                <div class="text-center">
                    <div class="text-6xl mb-4">🚧</div>
                    <h2 class="text-xl font-semibold mb-2">Coming Soon</h2>
                    <p class="text-muted-foreground">
                        {{ tabs.find(t => t.key === activeTab)?.label }} feature is under development
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Confirmation Dialog -->
    <ConfirmationDialog
        v-model:open="showSendDialog"
        :title="props.proposal.sent_at ? 'Resend Proposal' : 'Send Proposal'"
        :description="props.proposal.sent_at ? 'Are you sure you want to resend this proposal to the assigned contact? This will send them another email with a link to view the proposal.' : 'Are you sure you want to send this proposal to the assigned contact? This will send them an email with a link to view the proposal.'"
        :confirm-text="props.proposal.sent_at ? 'Resend Proposal' : 'Send Proposal'"
        cancel-text="Cancel"
        variant="default"
        @confirm="sendProposal"
        @cancel="showSendDialog = false"
    />
</template>
