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
import type { Proposal } from '@/types/models/proposal';

const props = defineProps<{
    proposals: Proposal[];
}>();

const { format: formatCurrency } = useCurrency();

const formatDate = (value: string | null): string => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString();
};
</script>

<template>
    <div
        v-if="props.proposals.length === 0"
        class="py-8 text-center text-sm text-muted-foreground"
    >
        No proposals yet.
    </div>
    <Table v-else>
        <TableHeader>
            <TableRow>
                <TableHead>Number</TableHead>
                <TableHead>Title</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Valid Until</TableHead>
                <TableHead class="text-right">Total</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="proposal in props.proposals" :key="proposal.id">
                <TableCell class="font-medium">
                    {{ proposal.proposal_number || `#${proposal.id}` }}
                </TableCell>
                <TableCell>{{ proposal.title }}</TableCell>
                <TableCell>
                    <Badge
                        v-if="proposal.proposal_status"
                        :style="{
                            backgroundColor: `${proposal.proposal_status.color}20`,
                            color: proposal.proposal_status.color,
                        }"
                    >
                        {{ proposal.proposal_status.title }}
                    </Badge>
                </TableCell>
                <TableCell>{{ formatDate(proposal.valid_until) }}</TableCell>
                <TableCell class="text-right">
                    {{ formatCurrency(proposal.grand_total) }}
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
