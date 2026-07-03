<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useCurrency } from '@/composables/useCurrency';
import type { Invoice } from '@/types/models/invoice';

const props = defineProps<{
    invoices: Invoice[];
}>();

const { format: formatCurrency } = useCurrency();

const formatDate = (value: string | null): string => {
    if (!value) {
return '-';
}

    return new Date(value).toLocaleDateString();
};

const balance = (invoice: Invoice): number => {
    return invoice.grand_total - invoice.amount_paid;
};
</script>

<template>
    <div v-if="props.invoices.length === 0" class="py-8 text-center text-sm text-muted-foreground">
        No invoices yet.
    </div>
    <Table v-else>
        <TableHeader>
            <TableRow>
                <TableHead>Number</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Issued</TableHead>
                <TableHead>Due</TableHead>
                <TableHead class="text-right">Total</TableHead>
                <TableHead class="text-right">Balance</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="invoice in props.invoices" :key="invoice.id">
                <TableCell class="font-medium">
                    {{ invoice.invoice_number || `#${invoice.id}` }}
                </TableCell>
                <TableCell>
                    <Badge
                        v-if="invoice.invoice_status"
                        :style="{
                            backgroundColor: `${invoice.invoice_status.color}20`,
                            color: invoice.invoice_status.color,
                        }"
                    >
                        {{ invoice.invoice_status.title }}
                    </Badge>
                </TableCell>
                <TableCell>{{ formatDate(invoice.issue_date) }}</TableCell>
                <TableCell>{{ formatDate(invoice.due_date) }}</TableCell>
                <TableCell class="text-right">
                    {{ formatCurrency(invoice.grand_total) }}
                </TableCell>
                <TableCell class="text-right">
                    <span
                        :class="
                            balance(invoice) > 0
                                ? 'text-rose-600'
                                : 'text-emerald-600'
                        "
                    >
                        {{ balance(invoice) > 0 ? formatCurrency(balance(invoice)) + ' due' : 'Paid' }}
                    </span>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
