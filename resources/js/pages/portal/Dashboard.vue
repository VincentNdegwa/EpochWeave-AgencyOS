<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, LogOut, ReceiptText } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { useCurrency } from '@/composables/useCurrency';
import { useDateFormat } from '@/composables/useDateFormat';
import type { Invoice } from '@/types/models/invoice';
import type { Proposal } from '@/types/models/proposal';

defineOptions({ layout: null });

const props = defineProps<{
    proposals: Proposal[];
    invoices: Invoice[];
}>();

const { format: formatCurrency } = useCurrency();
const { formatDate } = useDateFormat();
</script>

<template>
    <Head title="Client Portal" />

    <div class="min-h-screen bg-muted/30">
        <header class="border-b bg-card px-6 py-4">
            <div class="mx-auto flex max-w-5xl items-center justify-between">
                <h1 class="text-lg font-semibold">Client Portal</h1>
                <form method="post" action="/portal/logout">
                    <Button type="submit" variant="outline" size="sm" class="gap-1">
                        <LogOut class="h-4 w-4" />
                        Logout
                    </Button>
                </form>
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-8 p-6">
            <div>
                <h2 class="mb-4 text-sm font-semibold text-muted-foreground">Your Proposals</h2>
                <div class="space-y-2">
                    <div
                        v-for="proposal in proposals"
                        :key="proposal.id"
                        class="flex items-center justify-between rounded-lg border bg-card p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-blue-50">
                                <FileText class="h-4 w-4 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ proposal.title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(proposal.created_at) }}
                                    <span v-if="proposal.proposal_status">&middot; {{ proposal.proposal_status.title }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold">{{ formatCurrency(proposal.grand_total) }}</span>
                    </div>
                    <p v-if="!proposals.length" class="text-sm text-muted-foreground">No proposals yet.</p>
                </div>
            </div>

            <div>
                <h2 class="mb-4 text-sm font-semibold text-muted-foreground">Your Invoices</h2>
                <div class="space-y-2">
                    <div
                        v-for="invoice in invoices"
                        :key="invoice.id"
                        class="flex items-center justify-between rounded-lg border bg-card p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-50">
                                <ReceiptText class="h-4 w-4 text-amber-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ invoice.invoice_number }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(invoice.issue_date) }}
                                    <span v-if="invoice.status">&middot; {{ invoice.status.title }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">{{ formatCurrency(invoice.grand_total) }}</p>
                            <p v-if="invoice.amount_paid > 0" class="text-xs text-emerald-600">
                                Paid {{ formatCurrency(invoice.amount_paid) }}
                            </p>
                        </div>
                    </div>
                    <p v-if="!invoices.length" class="text-sm text-muted-foreground">No invoices yet.</p>
                </div>
            </div>
        </main>
    </div>
</template>
