<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { useCurrency } from '@/composables/useCurrency';
import { useDateFormat } from '@/composables/useDateFormat';
import type { Invoice } from '@/types/models/invoice';

const props = defineProps<{ invoice: Invoice }>();

const { format: fmt } = useCurrency();
const { formatDateLong } = useDateFormat();

const f = (amount: number | null | undefined) =>
    fmt(amount ?? 0, { currency: props.invoice.currency ?? 'USD' });

const invoiceNumber = computed(() =>
    props.invoice.invoice_number ? `#${props.invoice.invoice_number}` : `#${props.invoice.id}`,
);

const contact = computed(() => props.invoice.account_contact);
const account = computed(() => props.invoice.account);
const workspace = computed(() => props.invoice.workspace);

const hasDiscount = computed(() => (props.invoice.discount_total ?? 0) > 0);
const hasTax = computed(() => (props.invoice.total_tax_amount ?? 0) > 0);

const dueStatus = computed(() => {
    if (!props.invoice.due_date) return null;
    const diff = Math.ceil((new Date(props.invoice.due_date).getTime() - Date.now()) / 86_400_000);
    if (props.invoice.status === 'paid') return null;
    if (diff < 0) return { label: `Overdue by ${Math.abs(diff)} day${Math.abs(diff) === 1 ? '' : 's'}`, variant: 'destructive' as const };
    if (diff === 0) return { label: 'Due today', variant: 'destructive' as const };
    if (diff <= 3) return { label: `Due in ${diff} days`, variant: 'secondary' as const };
    return null;
});
</script>

<template>
    <div class="mx-auto w-full max-w-4xl bg-white text-slate-900">
        <div class="flex items-start justify-between px-5 pt-10 pb-8">
            <div class="space-y-1">
                <img
                    v-if="workspace?.logo_url"
                    :src="workspace.logo_url"
                    :alt="workspace?.name ?? 'Logo'"
                    class="mb-3 h-10 w-auto object-contain"
                />
                <p v-else class="mb-3 text-xl font-bold text-slate-900">
                    {{ workspace?.name ?? 'Your Company' }}
                </p>
                <p v-if="workspace?.address" class="text-xs text-slate-500 leading-relaxed">{{ workspace.address }}</p>
                <p v-if="workspace?.email" class="text-xs text-slate-500">{{ workspace.email }}</p>
                <p v-if="workspace?.phone" class="text-xs text-slate-500">{{ workspace.phone }}</p>
                <p v-if="workspace?.tax_number" class="text-xs text-slate-500">Tax No: {{ workspace.tax_number }}</p>
            </div>
            <div class="text-right space-y-1">
                <p class="text-3xl font-bold tracking-tight text-slate-900">INVOICE</p>
                <p class="font-mono text-base font-semibold text-slate-700">{{ invoiceNumber }}</p>
                <Badge v-if="dueStatus" :variant="dueStatus.variant" class="mt-2">
                    {{ dueStatus.label }}
                </Badge>
            </div>
        </div>

        <div class="mx-5 mb-8 grid grid-cols-3 divide-x divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Issue date</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">{{ formatDateLong(invoice.issue_date) }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Due date</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">{{ formatDateLong(invoice.due_date) }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Status</p>
                <p class="mt-1 text-sm font-semibold capitalize text-slate-800">{{ invoice.status ?? 'Draft' }}</p>
            </div>
        </div>

        <div class="mx-5 mb-8 grid grid-cols-2 gap-8">
            <div class="space-y-1">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Billed to</p>
                <p class="text-sm font-bold text-slate-900">{{ account?.company_name ?? 'Client' }}</p>
                <template v-if="contact">
                    <p class="text-sm text-slate-700">{{ contact.first_name }} {{ contact.last_name }}</p>
                    <p v-if="contact.job_title" class="text-xs text-slate-500">{{ contact.job_title }}</p>
                    <p class="text-xs text-slate-500">{{ contact.email }}</p>
                    <p v-if="contact.phone" class="text-xs text-slate-500">{{ contact.phone }}</p>
                </template>
            </div>
            <div class="space-y-1 text-right">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Payment reference</p>
                <p class="font-mono text-sm font-semibold text-slate-800">{{ invoiceNumber }}</p>
                <p v-if="invoice.payment_reference" class="text-xs text-slate-500 mt-1">Ref: {{ invoice.payment_reference }}</p>
            </div>
        </div>

        <div class="mx-5 mb-6">
            <div class="grid grid-cols-[1fr_60px_100px_100px] gap-3 rounded-t-md bg-slate-900 px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-white">
                <span>Description</span>
                <span class="text-center">Qty</span>
                <span class="text-right">Unit price</span>
                <span class="text-right">Amount</span>
            </div>
            <div class="divide-y divide-slate-100 border border-t-0 border-slate-200 rounded-b-md overflow-hidden">
                <div
                    v-for="item in invoice.items ?? []"
                    :key="item.id"
                    class="grid grid-cols-[1fr_60px_100px_100px] gap-3 items-start px-4 py-4"
                >
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ item.item_name }}</p>
                        <p v-if="item.description" class="mt-0.5 text-xs text-slate-500 leading-relaxed">{{ item.description }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <Badge v-if="item.discount_amount && item.discount_amount > 0" variant="destructive" class="text-[10px]">
                                −{{ f(item.discount_amount) }} discount
                            </Badge>
                        </div>
                    </div>
                    <p class="text-center text-sm text-slate-700">
                        {{ item.quantity }}<span v-if="item.unit_label" class="text-slate-400"> {{ item.unit_label }}</span>
                    </p>
                    <p class="text-right text-sm text-slate-700">{{ f(item.unit_price) }}</p>
                    <p class="text-right text-sm font-semibold text-slate-900">{{ f(item.total) }}</p>
                </div>
                <div v-if="!invoice.items?.length" class="px-4 py-8 text-center text-sm text-slate-400">
                    No line items.
                </div>
            </div>
        </div>

        <div class="mx-5 mb-8 flex justify-end">
            <div class="w-72 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span>{{ f(invoice.subtotal) }}</span>
                </div>
                <div v-if="hasDiscount" class="flex justify-between text-red-500">
                    <span>Discount</span>
                    <span>−{{ f(invoice.discount_total) }}</span>
                </div>
                <div v-if="hasTax" class="flex justify-between text-slate-600">
                    <span>Tax</span>
                    <span>{{ f(invoice.total_tax_amount) }}</span>
                </div>
                <Separator class="my-2 bg-slate-200" />
                <div class="flex justify-between text-base font-bold text-slate-900">
                    <span>Total</span>
                    <span>{{ f(invoice.grand_total) }}</span>
                </div>
                <template v-if="invoice.status === 'paid'">
                    <div class="flex justify-between text-green-600 font-medium">
                        <span>Paid</span>
                        <span>{{ f(invoice.grand_total) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900">
                        <span>Balance due</span>
                        <span>{{ f(0) }}</span>
                    </div>
                </template>
                <div v-else class="flex justify-between font-bold text-slate-900">
                    <span>Balance due</span>
                    <span>{{ f(invoice.grand_total) }}</span>
                </div>
            </div>
        </div>

        <div v-if="invoice.notes || workspace?.payment_instructions" class="mx-5 mb-10 space-y-4">
            <Separator class="bg-slate-200" />
            <div v-if="workspace?.payment_instructions" class="rounded-md border border-slate-200 bg-slate-50 px-5 py-4">
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Payment instructions</p>
                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ workspace.payment_instructions }}</p>
            </div>
            <div v-if="invoice.notes" class="rounded-md border border-slate-200 bg-slate-50 px-5 py-4">
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Notes</p>
                <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ invoice.notes }}</p>
            </div>
        </div>

        <div class="border-t border-slate-200 px-5 py-6 text-center">
            <p class="text-xs text-slate-400">
                Thank you for your business · {{ workspace?.name ?? 'EpochWeave' }}
                <span v-if="workspace?.email"> · {{ workspace.email }}</span>
            </p>
        </div>
    </div>
</template>
