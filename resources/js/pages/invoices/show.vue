<script setup lang="ts">
import { Head, Link, router, setLayoutProps } from '@inertiajs/vue3';
import {
    Send, CheckCircle2Icon, ClockIcon, CalendarIcon,
    BuildingIcon, UserIcon, FileTextIcon, FolderOpenIcon,
    ExternalLinkIcon, HashIcon,
} from '@lucide/vue';
import { computed, ref, watchEffect } from 'vue';

import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import { Badge }         from '@/components/ui/badge';
import { Button }        from '@/components/ui/button';
import { Separator }     from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { useCurrency }       from '@/composables/useCurrency';
import { useDateFormat }     from '@/composables/useDateFormat';
import { useInvoiceStatuses } from '@/composables/useEnums';
import { dashboard }         from '@/routes';
import type { Invoice }      from '@/types/models/invoice';
import InvoiceDocument from './components/InvoiceDocument.vue';
import InvoiceActions from './components/InvoiceActions.vue';
const props = defineProps<{ invoice: Invoice }>();

const { format: fmt }  = useCurrency();
const { formatDate, formatDateTime } = useDateFormat();
const invoiceStatuses  = useInvoiceStatuses();

watchEffect(() => {
    setLayoutProps({
        title:       `Invoice ${props.invoice.invoice_number ? '#' + props.invoice.invoice_number : ''}`,
        description: 'Invoice details',
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Invoices',  href: InvoiceController.index() },
            { title: `#${props.invoice.invoice_number ?? props.invoice.id}` },
        ],
    });
});

const status = computed(() =>
    invoiceStatuses.getByValue(props.invoice.status) ?? { label: props.invoice.status ?? 'Draft', hexColor: '#6b7280' },
);

const invoiceNumber = computed(() =>
    props.invoice.invoice_number ? `#${props.invoice.invoice_number}` : `#${props.invoice.id}`,
);

const formattedTotal = computed(() =>
    fmt(props.invoice.grand_total ?? 0, { currency: props.invoice.currency ?? 'USD' }),
);

const dueHint = computed(() => {
    if (!props.invoice.due_date || props.invoice.status === 'paid') return null;
    const diff = Math.ceil((new Date(props.invoice.due_date).getTime() - Date.now()) / 86_400_000);
    if (diff < 0)   return { label: `Overdue by ${Math.abs(diff)}d`, cls: 'text-destructive' };
    if (diff === 0) return { label: 'Due today',                     cls: 'text-destructive' };
    if (diff <= 3)  return { label: `Due in ${diff}d`,               cls: 'text-amber-600'   };
    return null;
});

const timelineEntries = computed(() =>
    [
        { label: 'Created',  date: props.invoice.created_at, icon: ClockIcon,        cls: 'text-muted-foreground' },
        { label: 'Sent',     date: props.invoice.sent_at,    icon: Send,             cls: 'text-blue-500'         },
        { label: 'Paid',     date: props.invoice.paid_at,    icon: CheckCircle2Icon, cls: 'text-green-500'        },
        { label: 'Voided',   date: props.invoice.voided_at,  icon: FileTextIcon,     cls: 'text-destructive'      },
    ].filter(e => Boolean(e.date)),
);

function initials(name: string) {
    return name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
}
</script>

<template>
    <Head :title="`Invoice ${invoiceNumber}`" />

    <div class="flex flex-col">

        <div class="sticky top-0 z-30 border-b pb-4 border-border bg-background/95 backdrop-blur-sm print:hidden">
            <div class="flex flex-wrap items-center justify-between gap-3">

                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            :style="{ backgroundColor: status.hexColor, color: '#fff' }"
                            class="rounded-md text-[11px]"
                        >
                            {{ status.label }}
                        </Badge>
                        <span class="inline-flex items-center gap-1 rounded-md border border-border
                                     bg-muted/50 px-2 py-0.5 font-mono text-[11px] text-muted-foreground">
                            <HashIcon class="h-2.5 w-2.5" />{{ invoice.invoice_number ?? invoice.id }}
                        </span>
                        <span v-if="dueHint" class="text-[11px] font-semibold" :class="dueHint.cls">
                            {{ dueHint.label }}
                        </span>
                    </div>
                    <h1 class="text-base font-semibold text-foreground">
                        {{ invoice.account?.company_name ?? 'Invoice' }}
                        <span class="font-normal text-muted-foreground">· {{ formattedTotal }}</span>
                    </h1>
                </div>

                <InvoiceActions
                    :invoice="invoice"
                    variant="split"
                    size="sm"
                />
            </div>
        </div>

        <div class="border-b border-border bg-background print:hidden">
            <div class="grid grid-cols-2 divide-x divide-border sm:grid-cols-4">
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Total</p>
                    <p class="mt-0.5 text-xl font-bold tabular-nums text-foreground">{{ formattedTotal }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Issue date</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ formatDate(invoice.issue_date) }}</p>
                    <p class="text-[10px] text-muted-foreground">Issued</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Due date</p>
                    <p class="mt-0.5 text-base font-bold text-foreground" :class="dueHint?.cls">
                        {{ formatDate(invoice.due_date) }}
                    </p>
                    <p class="text-[10px]" :class="dueHint ? dueHint.cls : 'text-muted-foreground'">
                        {{ dueHint?.label ?? 'On time' }}
                    </p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Items</p>
                    <p class="mt-0.5 text-xl font-bold text-foreground">{{ invoice.items?.length ?? 0 }}</p>
                    <p class="text-[10px] text-muted-foreground">Line items</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-8 lg:flex-row lg:items-start">

            <div class="flex-1">
              <InvoiceDocument :invoice="invoice" />
            </div>

            <aside class="w-full space-y-6 mt-2 lg:w-72 lg:flex-shrink-0 print:hidden">

                <div class="space-y-1">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">People</p>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3 py-1.5">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-md bg-muted">
                                <BuildingIcon class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground">Account</p>
                                <p class="text-sm font-medium truncate">{{ invoice.account?.company_name ?? '—' }}</p>
                            </div>
                        </div>

                        <div v-if="invoice.account_contact" class="flex items-center gap-3 py-1.5">
                            <Avatar class="h-8 w-8 flex-shrink-0">
                                <AvatarFallback class="text-[10px]">
                                    {{ initials(`${invoice.account_contact.first_name} ${invoice.account_contact.last_name}`) }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground">Contact</p>
                                <p class="text-sm font-medium">{{ invoice.account_contact.first_name }} {{ invoice.account_contact.last_name }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ invoice.account_contact.email }}</p>
                            </div>
                        </div>

                        <div v-if="invoice.user" class="flex items-center gap-3 py-1.5">
                            <Avatar class="h-8 w-8 flex-shrink-0">
                                <AvatarFallback class="text-[10px]">{{ initials(invoice.user.name) }}</AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground">Owner</p>
                                <p class="text-sm font-medium truncate">{{ invoice.user.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <Separator />

                <div class="space-y-1">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Linked</p>
                    <div class="space-y-0.5">
                        <Link
                            v-if="invoice.proposal"
                            :href="`/proposals/${invoice.proposal.id}`"
                            class="group flex items-center gap-3 rounded-md px-2 py-2 transition-colors hover:bg-muted"
                        >
                            <FileTextIcon class="h-4 w-4 text-muted-foreground" />
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-muted-foreground">Proposal</p>
                                <p class="text-sm font-medium truncate group-hover:text-primary transition-colors">{{ invoice.proposal.title }}</p>
                            </div>
                            <ExternalLinkIcon class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground/50" />
                        </Link>

                        <Link
                            v-if="invoice.project"
                            :href="`/projects/${invoice.project.id}`"
                            class="group flex items-center gap-3 rounded-md px-2 py-2 transition-colors hover:bg-muted"
                        >
                            <FolderOpenIcon class="h-4 w-4 text-muted-foreground" />
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-muted-foreground">Project</p>
                                <p class="text-sm font-medium truncate group-hover:text-primary transition-colors">{{ invoice.project.name }}</p>
                            </div>
                            <ExternalLinkIcon class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground/50" />
                        </Link>
                    </div>
                </div>

                <Separator />

                <div class="space-y-1">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Timeline</p>

                    <div v-if="timelineEntries.length" class="relative space-y-0 pl-6">
                        <div class="absolute left-[8px] top-1.5 bottom-1.5 w-px bg-border" />
                        <div
                            v-for="entry in timelineEntries"
                            :key="entry.label"
                            class="relative flex items-start gap-3 pb-4 last:pb-0"
                        >
                            <div class="absolute -left-[25px] top-0 flex h-5 w-5 items-center justify-center rounded-full border bg-background">
                                <component :is="entry.icon" class="h-3 w-3" :class="entry.cls" />
                            </div>
                            <div>
                                <p class="text-xs font-medium">{{ entry.label }}</p>
                                <p class="text-[11px] text-muted-foreground">{{ formatDateTime(entry.date) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="rounded-md border border-dashed border-border py-4 text-center text-xs text-muted-foreground">
                        No events yet.
                    </div>
                </div>

            </aside>
        </div>
    </div>
</template>

<style>
@media print {
    header, nav, aside, .print\:hidden { display: none !important; }
    body { background: white; }
}
</style>