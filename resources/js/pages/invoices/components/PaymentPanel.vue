<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CreditCard, Trash2, DollarSign } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useCurrency } from '@/composables/useCurrency';
import type { CreditNote } from '@/types/models/credit_note';
import type { Payment } from '@/types/models/payment';

const props = defineProps<{
    invoiceId: number;
    grandTotal: number;
    amountPaid: number;
    payments: Payment[];
    creditNotes: CreditNote[];
}>();

const { format: formatCurrency } = useCurrency();

const amount = ref('');
const method = ref('bank_transfer');
const reference = ref('');
const notes = ref('');
const paidAt = ref(new Date().toISOString().split('T')[0]);

const totalRefunded = computed(() =>
    props.creditNotes.reduce((sum, cn) => sum + (cn.amount ?? 0), 0),
);

const actualBalance = computed(() =>
    props.grandTotal - props.amountPaid,
);

const submitPayment = () => {
    if (!amount.value || parseFloat(amount.value) <= 0) {
return;
}

    router.post(`/invoices/${props.invoiceId}/payments`, {
        amount: parseFloat(amount.value),
        method: method.value,
        paid_at: paidAt.value,
        reference: reference.value || null,
        notes: notes.value || null,
    }, {
        preserveScroll: true,
        onFinish: () => {
            amount.value = '';
            reference.value = '';
            notes.value = '';
        },
    });
};

const deletePayment = (paymentId: number) => {
    router.delete(`/invoices/${props.invoiceId}/payments/${paymentId}`, {
        preserveScroll: true,
    });
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <CreditCard class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold">Payments</h3>
                <span class="text-xs text-muted-foreground">({{ payments.length }})</span>
            </div>
            <span class="text-sm font-medium" :class="actualBalance > 0 ? 'text-destructive' : 'text-emerald-600'">
                Balance: {{ formatCurrency(actualBalance) }}
            </span>
        </div>

        <div v-if="actualBalance > 0" class="rounded-lg border p-4">
            <div class="grid gap-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Label class="text-xs">Amount</Label>
                        <Input v-model="amount" type="number" step="0.01" placeholder="0.00" />
                    </div>
                    <div>
                        <Label class="text-xs">Method</Label>
                        <select v-model="method" class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div>
                    <Label class="text-xs">Date</Label>
                    <Input v-model="paidAt" type="date" />
                </div>
                <div>
                    <Label class="text-xs">Reference</Label>
                    <Input v-model="reference" placeholder="Transaction reference" />
                </div>
                <div>
                    <Label class="text-xs">Notes</Label>
                    <Textarea v-model="notes" rows="2" placeholder="Optional notes" />
                </div>
                <div class="flex justify-end">
                    <Button size="sm" :disabled="!amount || parseFloat(amount) <= 0" @click="submitPayment">
                        <DollarSign class="h-3.5 w-3.5 mr-1" />
                        Record Payment
                    </Button>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <div
                v-for="payment in payments"
                :key="payment.id"
                class="flex items-center justify-between rounded-lg border p-3"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium">{{ formatCurrency(payment.amount) }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatDate(payment.paid_at) }} &middot; {{ payment.method.replace('_', ' ') }}
                        <span v-if="payment.reference">&middot; {{ payment.reference }}</span>
                        <span v-if="payment.user">&middot; {{ payment.user.name }}</span>
                    </p>
                </div>
                <Button
                    size="icon"
                    variant="ghost"
                    class="h-7 w-7 shrink-0 text-muted-foreground hover:text-destructive"
                    @click="deletePayment(payment.id)"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </Button>
            </div>

            <p v-if="!payments.length" class="text-sm text-muted-foreground">No payments recorded.</p>
        </div>

        <div v-if="creditNotes.length > 0" class="space-y-2">
            <div class="flex items-center gap-2">
                <CreditCard class="h-4 w-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold">Credit Notes / Refunds</h3>
                <span class="text-xs text-muted-foreground">({{ creditNotes.length }})</span>
            </div>
            <div
                v-for="note in creditNotes"
                :key="note.id"
                class="flex items-center justify-between rounded-lg border border-destructive/20 bg-destructive/5 p-3"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium text-destructive">-{{ formatCurrency(note.amount) }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatDate(note.refunded_at) }}
                        <span v-if="note.reference">&middot; {{ note.reference }}</span>
                        <span v-if="note.reason">&middot; {{ note.reason }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
