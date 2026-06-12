<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import {
    Building2,
    CalendarDays,
    CheckCircle2,
    Clock4,
    Eye,
    Pencil,
    Send,
} from '@lucide/vue';
import { computed, onMounted } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrency } from '@/composables/useCurrency';
import ProposalCanvas from '@/pages/proposals/components/canvas/ProposalCanvas.vue';
import { dashboard } from '@/routes';
import { show as accountShow } from '@/routes/accounts';
import { edit as proposalEdit } from '@/routes/proposals';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useWorkspaceStore } from '@/stores/workspace';
import type { Proposal } from '@/types/models/proposal';

const props = defineProps<{
    proposal: Proposal;
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

const timelineEntries = computed(() =>
    [
        { label: 'Created', date: props.proposal.created_at, icon: Clock4 },
        { label: 'Sent', date: props.proposal.sent_at, icon: Send },
        {
            label: 'Viewed',
            date: props.proposal.viewed_at ?? props.proposal.last_viewed_at,
            icon: Eye,
        },
        {
            label: 'Decided',
            date: props.proposal.decided_at,
            icon: CheckCircle2,
        },
        {
            label: 'Expired',
            date: props.proposal.expired_at,
            icon: CalendarDays,
        },
    ].filter((entry) => Boolean(entry.date)),
);

setLayoutProps({
    title: 'proposal preview',
    description: 'Review the live client experience and supporting details.',
    breadcrumbs: [
        { title: 'dashboard', href: dashboard() },
        { title: 'proposals', href: '/proposals' },
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

    <div
        class="-mx-4 -mb-4 flex h-[calc(100vh-64px)] flex-col bg-background lg:flex-row"
    >
        <div class="relative flex-1 bg-muted/10">
            <ProposalCanvas :is-locked="true" :builder-mode="'proposal'" />
        </div>

        <aside
            class="w-full border-t border-border bg-background/95 p-6 shadow-2xl backdrop-blur lg:w-96 lg:border-t-0 lg:border-l custom-scrollbar overflow-y-auto"
        >
            <div class="space-y-8">
                <section class="space-y-3">
                    <div class="flex flex-col gap-2">
                        <h1
                            class="text-2xl leading-snug font-semibold text-foreground"
                        >
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
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Updated
                            {{ formatDateTime(props.proposal.updated_at) }} •
                            Views {{ props.proposal.view_count ?? 0 }}
                        </p>
                    </div>
                    <div class="flex justify-end">
                        <Link
                            :href="proposalEdit(props.proposal.id).url"
                            class="w-full"
                        >
                            <Button class="w-full gap-2">
                                <Pencil class="h-4 w-4" />
                                Edit
                            </Button>
                        </Link>
                    </div>
                    <Separator />
                </section>

                <section class="space-y-3">
                    <div
                        class="flex items-center justify-between text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        <span>Financials</span>
                        <span>{{ props.proposal.currency ?? 'USD' }}</span>
                    </div>
                    <Separator />
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-muted-foreground uppercase">
                                Total value
                            </p>
                            <p class="text-2xl font-semibold text-foreground">
                                {{ formattedValue }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase">
                                Deposit
                            </p>
                            <p class="text-sm text-foreground">
                                {{ depositSummary }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase">
                                Valid until
                            </p>
                            <p class="text-sm font-medium text-foreground">
                                {{ validUntilDisplay }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ validityStatus }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="space-y-3">
                    <p
                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Account
                    </p>
                    <Separator />
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
                    
                    <div v-if="props.proposal.account_contact" class="flex items-start gap-2 text-sm">
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
                </section>

                <section class="space-y-3">
                    <p
                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Team
                    </p>
                    <Separator />
                    <div v-if="props.proposal.user" class="flex items-start gap-2 text-sm">
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
                </section>

                <section class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Activity
                        </p>
                        <Badge variant="outline" class="text-xs">
                            {{ timelineEntries.length }} events
                        </Badge>
                    </div>
                    <Separator />
                    <div v-if="timelineEntries.length" class="space-y-3">
                        <div
                            v-for="entry in timelineEntries"
                            :key="entry.label"
                            class="flex items-center gap-3 rounded-lg border border-border/70 px-3 py-2"
                        >
                            <component
                                :is="entry.icon"
                                class="h-4 w-4 text-muted-foreground"
                            />
                            <div>
                                <p class="text-sm font-medium">
                                    {{ entry.label }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDateTime(entry.date) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="rounded-lg border border-dashed border-border px-4 py-6 text-center text-xs text-muted-foreground"
                    >
                        No activity recorded yet.
                    </div>
                </section>
            </div>
        </aside>
    </div>
</template>
