<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import {
    BuildingIcon,
    FileTextIcon,
    FolderOpenIcon,
    ExternalLinkIcon,
    HashIcon,
} from '@lucide/vue';
import { computed, watchEffect } from 'vue';

import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { StatBar } from '@/components/ui/stat-bar';
import { useCurrency } from '@/composables/useCurrency';
import { useDateFormat } from '@/composables/useDateFormat';
import { dashboard } from '@/routes';
import type { Invoice } from '@/types/models/invoice';
import type { InvoiceStatus } from '@/types/models/invoice_status';
import InvoiceActions from './components/InvoiceActions.vue';
import InvoiceDocument from './components/InvoiceDocument.vue';
import PaymentPanel from './components/PaymentPanel.vue';
const props = defineProps<{
    invoice: Invoice;
    invoice_statuses: InvoiceStatus[];
    activities: {
        id: number;
        type: string;
        description: string;
        created_at: string;
        user?: { id: number; name: string } | null;
    }[];
}>();

const { format: fmt } = useCurrency();
const { formatDate } = useDateFormat();
watchEffect(() => {
    setLayoutProps({
        title: `Invoice ${props.invoice.invoice_number ? '#' + props.invoice.invoice_number : ''}`,
        description: 'Invoice details',
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Invoices', href: InvoiceController.index() },
            { title: `#${props.invoice.invoice_number ?? props.invoice.id}` },
        ],
    });
});

const status = computed(() => ({
    label: props.invoice.invoice_status?.title ?? 'Draft',
    hexColor: props.invoice.invoice_status?.color ?? '#6b7280',
}));

const invoiceNumber = computed(() =>
    props.invoice.invoice_number
        ? `#${props.invoice.invoice_number}`
        : `#${props.invoice.id}`,
);

const formattedTotal = computed(() =>
    fmt(props.invoice.grand_total ?? 0, {
        currency: props.invoice.currency ?? 'USD',
    }),
);

const dueHint = computed(() => {
    if (
        !props.invoice.due_date ||
        props.invoice.invoice_status?.automation_trigger === 'paid'
    ) {
        return null;
    }

    const diff = Math.ceil(
        (new Date(props.invoice.due_date).getTime() - Date.now()) / 86_400_000,
    );

    if (diff < 0) {
        return {
            label: `Overdue by ${Math.abs(diff)}d`,
            cls: 'text-destructive',
        };
    }

    if (diff === 0) {
        return { label: 'Due today', cls: 'text-destructive' };
    }

    if (diff <= 3) {
        return { label: `Due in ${diff}d`, cls: 'text-amber-600' };
    }

    return null;
});

function initials(name: string) {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}
</script>

<template>
    <Head :title="`Invoice ${invoiceNumber}`" />

    <div class="flex flex-col">
        <div
            class="sticky top-0 z-30 bg-background/95 pb-4 backdrop-blur-sm print:hidden"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            :style="{
                                backgroundColor: status.hexColor,
                                color: '#fff',
                            }"
                            class="rounded-md text-[11px]"
                        >
                            {{ status.label }}
                        </Badge>
                        <span
                            class="inline-flex items-center gap-1 rounded-md border border-border bg-muted/50 px-2 py-0.5 font-mono text-[11px] text-muted-foreground"
                        >
                            <HashIcon class="h-2.5 w-2.5" />{{
                                invoice.invoice_number ?? invoice.id
                            }}
                        </span>
                        <span
                            v-if="dueHint"
                            class="text-[11px] font-semibold"
                            :class="dueHint.cls"
                        >
                            {{ dueHint.label }}
                        </span>
                    </div>
                    <h1 class="text-base font-semibold text-foreground">
                        {{ invoice.account?.company_name ?? 'Invoice' }}
                        <span class="font-normal text-muted-foreground"
                            >· {{ formattedTotal }}</span
                        >
                    </h1>
                </div>

                <InvoiceActions
                    :invoice="invoice"
                    :invoice_statuses="props.invoice_statuses"
                    variant="split"
                    size="sm"
                />
            </div>
        </div>

        <StatBar
            :items="[
                { label: 'Total', value: formattedTotal },
                { label: 'Issue Date', value: formatDate(invoice.issue_date) },
                { label: 'Due Date', value: formatDate(invoice.due_date) },
                { label: 'Items', value: invoice.items?.length ?? 0 },
            ]"
        />

        <div class="flex flex-col gap-8 lg:flex-row lg:items-start">
            <div class="flex-1">
                <InvoiceDocument :invoice="invoice" />
            </div>

            <aside
                class="mt-2 w-full space-y-6 lg:w-72 lg:flex-shrink-0 print:hidden"
            >
                <div class="space-y-1">
                    <p
                        class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        People
                    </p>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3 py-1.5">
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-muted"
                            >
                                <BuildingIcon
                                    class="h-4 w-4 text-muted-foreground"
                                />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground">
                                    Account
                                </p>
                                <p class="truncate text-sm font-medium">
                                    {{ invoice.account?.company_name ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="invoice.account_contact"
                            class="flex items-center gap-3 py-1.5"
                        >
                            <Avatar class="h-8 w-8 flex-shrink-0">
                                <AvatarFallback class="text-[10px]">
                                    {{
                                        initials(
                                            `${invoice.account_contact.first_name} ${invoice.account_contact.last_name}`,
                                        )
                                    }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground">
                                    Contact
                                </p>
                                <p class="text-sm font-medium">
                                    {{ invoice.account_contact.first_name }}
                                    {{ invoice.account_contact.last_name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ invoice.account_contact.email }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="invoice.user"
                            class="flex items-center gap-3 py-1.5"
                        >
                            <Avatar class="h-8 w-8 flex-shrink-0">
                                <AvatarFallback class="text-[10px]">{{
                                    initials(invoice.user.name)
                                }}</AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground">
                                    Owner
                                </p>
                                <p class="truncate text-sm font-medium">
                                    {{ invoice.user.name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <Separator />

                <div class="space-y-1">
                    <p
                        class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Linked
                    </p>
                    <div class="space-y-0.5">
                        <Link
                            v-if="invoice.proposal"
                            :href="`/proposals/${invoice.proposal.id}`"
                            class="group flex items-center gap-3 rounded-md px-2 py-2 transition-colors hover:bg-muted"
                        >
                            <FileTextIcon
                                class="h-4 w-4 text-muted-foreground"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-muted-foreground">
                                    Proposal
                                </p>
                                <p
                                    class="truncate text-sm font-medium transition-colors group-hover:text-primary"
                                >
                                    {{ invoice.proposal.title }}
                                </p>
                            </div>
                            <ExternalLinkIcon
                                class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground/50"
                            />
                        </Link>

                        <Link
                            v-if="invoice.project"
                            :href="`/projects/${invoice.project.id}`"
                            class="group flex items-center gap-3 rounded-md px-2 py-2 transition-colors hover:bg-muted"
                        >
                            <FolderOpenIcon
                                class="h-4 w-4 text-muted-foreground"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-muted-foreground">
                                    Project
                                </p>
                                <p
                                    class="truncate text-sm font-medium transition-colors group-hover:text-primary"
                                >
                                    {{ invoice.project.name }}
                                </p>
                            </div>
                            <ExternalLinkIcon
                                class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground/50"
                            />
                        </Link>
                    </div>
                </div>

                <Separator />

                <PaymentPanel
                    :invoice-id="invoice.id"
                    :grand-total="invoice.grand_total"
                    :amount-paid="invoice.amount_paid"
                    :payments="invoice.payments || []"
                    :credit-notes="invoice.credit_notes || []"
                />

                <Separator />

                <div class="space-y-1">
                    <p
                        class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Activity
                    </p>
                    <ActivityTimeline :activities="props.activities" />
                </div>
            </aside>
        </div>
    </div>
</template>

<style>
@media print {
    header,
    nav,
    aside,
    .print\:hidden {
        display: none !important;
    }
    body {
        background: white;
    }
}
</style>
