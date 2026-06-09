<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Badge,
} from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { dashboard } from '@/routes';
import { edit as proposalEdit } from '@/routes/proposals';
import { show as accountShow } from '@/routes/accounts';
import { useCurrency } from '@/composables/useCurrency';
import type { Proposal } from '@/types/models/proposal';
import type {
  BaseBlock,
  CoverBlockData,
  RichTextBlockData,
  PricingTableBlockData,
  TimelineBlockData,
  TeamMemberBlockData,
  TestimonialBlockData,
  CtaBlockData,
} from '@/types/proposal-builder';
import { blockRegistry } from '@/pages/proposals/components/blocks/registry';
import {
  Building2,
  CalendarDays,
  CheckCircle2,
  Clock4,
  DollarSign,
  Eye,
  FileText,
  Layers,
  Send,
} from '@lucide/vue';

const props = defineProps<{
  proposal: Proposal;
}>();

const { format: formatCurrency } = useCurrency();

const registryMap = Object.fromEntries(
  blockRegistry.map((entry) => [entry.type, entry])
) as Record<string, (typeof blockRegistry)[number]>;

const sortedBlocks = computed<BaseBlock[]>(() => {
  if (!Array.isArray(props.proposal.content)) {
    return [];
  }
  return [...props.proposal.content].sort(
    (a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0)
  );
});

const blockSummaries = computed(() =>
  sortedBlocks.value.map((block, index) => {
    const entry = registryMap[block.type];
    return {
      id: block.id ?? `block-${index}`,
      label: entry?.label ?? block.type,
      description: summarizeBlock(block),
      icon: entry?.icon ?? FileText,
      index: index + 1,
    };
  }),
);

const hasBlocks = computed(() => blockSummaries.value.length > 0);

const statusMeta: Record<string, { label: string; variant: 'default' | 'secondary' | 'outline' | 'destructive' }> = {
  draft: { label: 'Draft', variant: 'secondary' },
  sent: { label: 'Sent', variant: 'outline' },
  viewed: { label: 'Viewed', variant: 'outline' },
  accepted: { label: 'Accepted', variant: 'default' },
  rejected: { label: 'Rejected', variant: 'destructive' },
  expired: { label: 'Expired', variant: 'secondary' },
};

const statusDisplay = computed(() => statusMeta[props.proposal.status] ?? { label: props.proposal.status, variant: 'secondary' });

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

  return formatCurrency(props.proposal.deposit_amount ?? props.proposal.deposit_value ?? 0, {
    currency: props.proposal.currency ?? 'USD',
  });
});

const proposalNumber = computed(() => props.proposal.proposal_number ?? props.proposal.token ?? 'DRAFT');

const validUntilDisplay = computed(() => formatDate(props.proposal.valid_until));
const validityStatus = computed(() => describeValidity(props.proposal.valid_until));

const accountName = computed(() => props.proposal.account?.company_name ?? 'Unassigned');
const accountHref = computed(() =>
  props.proposal.account?.id ? accountShow(props.proposal.account.id).url : null
);

const timelineEntries = computed(() =>
  [
    { label: 'Created', date: props.proposal.created_at, icon: Clock4 },
    { label: 'Sent', date: props.proposal.sent_at, icon: Send },
    { label: 'Viewed', date: props.proposal.viewed_at ?? props.proposal.last_viewed_at, icon: Eye },
    { label: 'Decided', date: props.proposal.decided_at, icon: CheckCircle2 },
    { label: 'Expired', date: props.proposal.expired_at, icon: CalendarDays },
  ].filter((entry) => Boolean(entry.date)),
);

setLayoutProps({
  title: 'proposal overview',
  description: 'Review lifecycle activity, recipients, and structured blocks.',
  breadcrumbs: [
    { title: 'dashboard', href: dashboard() },
    { title: 'proposals', href: '/proposals' },
    { title: props.proposal.title },
  ],
});

function summarizeBlock(block: BaseBlock): string {
  switch (block.type) {
    case 'cover': {
      const data = block.data as CoverBlockData;
      return data.heading || 'Cover hero';
    }
    case 'rich_text': {
      const data = block.data as RichTextBlockData;
      if (data.title) {
        return data.title;
      }
      return stripHtml(data.content).slice(0, 120) || 'Rich text section';
    }
    case 'pricing_table': {
      const data = block.data as PricingTableBlockData;
      const count = data.items?.length ?? 0;
      return count === 1 ? '1 line item' : `${count} line items`;
    }
    case 'timeline': {
      const data = block.data as TimelineBlockData;
      const count = data.milestones?.length ?? 0;
      return count ? `${count} milestones` : 'Timeline overview';
    }
    case 'team_member': {
      const data = block.data as TeamMemberBlockData;
      const count = data.members?.length ?? 0;
      return count ? `${count} team members` : 'Team spotlight';
    }
    case 'testimonial': {
      const data = block.data as TestimonialBlockData;
      const count = data.items?.length ?? 0;
      return count ? `${count} testimonials` : 'Client quotes';
    }
    case 'cta': {
      const data = block.data as CtaBlockData;
      return data.heading || data.button_text || 'Call to action';
    }
    default: {
      const entry = registryMap[block.type];
      return entry?.description ?? 'Custom content block';
    }
  }
}

function stripHtml(value: string): string {
  if (!value) {
    return '';
  }
  return value.replace(/<[^>]*>/g, '').trim();
}

function formatDate(value?: string | null): string {
  if (!value) {
    return '—';
  }
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value));
}

function formatDateTime(value?: string | null): string {
  if (!value) {
    return '—';
  }
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));
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

  <div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
      <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-3">
          <h1 class="text-3xl font-semibold tracking-tight">
            {{ props.proposal.title }}
          </h1>
          <Badge :variant="statusDisplay.variant">
            {{ statusDisplay.label }}
          </Badge>
          <Badge variant="outline" class="font-mono text-xs">
            #{{ proposalNumber }}
          </Badge>
        </div>
        <p class="text-sm text-muted-foreground">
          Last updated {{ formatDateTime(props.proposal.updated_at) }} • View count: {{ props.proposal.view_count ?? 0 }}
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <Link :href="proposalEdit(props.proposal.id).url" preserve-scroll>
          <Button class="gap-2">
            <Layers class="h-4 w-4" />
            Edit in builder
          </Button>
        </Link>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <Card class="lg:col-span-2">
        <CardContent class="space-y-4 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Content structure
              </p>
              <p class="text-sm text-muted-foreground">Snapshot of every block your client will see.</p>
            </div>
            <Badge variant="outline" class="text-xs">
              {{ blockSummaries.length }} blocks
            </Badge>
          </div>

          <div v-if="hasBlocks" class="space-y-3">
            <div
              v-for="block in blockSummaries"
              :key="block.id"
              class="flex items-start gap-4 rounded-xl border border-border/70 bg-card px-4 py-3"
            >
              <div class="mt-0.5 rounded-md bg-muted px-2 py-1 text-[11px] font-semibold text-muted-foreground">
                {{ `#${block.index.toString().padStart(2, '0')}` }}
              </div>
              <component :is="block.icon" class="mt-0.5 h-4 w-4 text-muted-foreground" />
              <div class="flex-1">
                <p class="text-sm font-semibold text-foreground">{{ block.label }}</p>
                <p class="text-xs text-muted-foreground">{{ block.description }}</p>
              </div>
            </div>
          </div>

          <div
            v-else
            class="rounded-xl border border-dashed border-border px-6 py-12 text-center text-sm text-muted-foreground"
          >
            This proposal does not have any blocks yet.
          </div>
        </CardContent>
      </Card>

      <div class="space-y-6">
        <Card>
          <CardContent class="space-y-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Snapshot</p>
            <div class="grid gap-3">
              <div class="rounded-lg border border-border/60 bg-muted/30 px-4 py-3">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                  <DollarSign class="h-3.5 w-3.5" />
                  Value
                </p>
                <p class="mt-1 text-2xl font-semibold">{{ formattedValue }}</p>
              </div>
              <div class="rounded-lg border border-border/60 bg-muted/30 px-4 py-3">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                  <Layers class="h-3.5 w-3.5" />
                  Deposit
                </p>
                <p class="mt-1 text-base font-medium text-foreground">{{ depositSummary }}</p>
              </div>
              <div class="rounded-lg border border-border/60 bg-muted/30 px-4 py-3">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                  <CalendarDays class="h-3.5 w-3.5" />
                  Valid until
                </p>
                <p class="mt-1 text-base font-medium text-foreground">{{ validUntilDisplay }}</p>
                <p class="text-xs text-muted-foreground">{{ validityStatus }}</p>
              </div>
              <div class="rounded-lg border border-border/60 bg-muted/30 px-4 py-3">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                  <Building2 class="h-3.5 w-3.5" />
                  Account
                </p>
                <div class="mt-1 text-base font-medium text-foreground">
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
          </CardContent>
        </Card>

        <Card>
          <CardContent class="space-y-4 p-6">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Activity</p>
              <Badge variant="outline" class="text-xs">
                {{ timelineEntries.length }} events
              </Badge>
            </div>
            <div v-if="timelineEntries.length" class="space-y-3">
              <div
                v-for="entry in timelineEntries"
                :key="entry.label"
                class="flex items-center gap-3 rounded-lg border border-border/70 px-3 py-2"
              >
                <component :is="entry.icon" class="h-4 w-4 text-muted-foreground" />
                <div>
                  <p class="text-sm font-medium">{{ entry.label }}</p>
                  <p class="text-xs text-muted-foreground">{{ formatDateTime(entry.date) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="rounded-lg border border-dashed border-border px-4 py-6 text-center text-sm text-muted-foreground">
              No activity recorded yet.
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
