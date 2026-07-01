<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useCurrency } from '@/composables/useCurrency';
import { useDateFormat } from '@/composables/useDateFormat';
import type { Invoice } from '@/types/models/invoice';
import type { Payment } from '@/types/models/payment';

interface Props {
    open: boolean;
    invoice: Invoice;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const { formatDateInput } = useDateFormat();
const { format: fmtCurrency } = useCurrency();

const isSubmitting = ref(false);
const errors = ref<Record<string, string>>({});

const payments = computed<Payment[]>(() => props.invoice.payments ?? []);

const maxRefundAmount = computed(() => {
    const totalPaid = props.invoice.amount_paid ?? 0;
    const totalRefunded = payments.value.reduce(
        (sum, p) => sum + (p.refunded_amount ?? 0),
        0,
    );

    return Math.max(totalPaid - totalRefunded, 0);
});

const form = ref({
    amount: '',
    payment_id: '',
    refunded_at: formatDateInput(new Date()),
    reason: '',
    reference: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            form.value = {
                amount:
                    maxRefundAmount.value > 0
                        ? maxRefundAmount.value.toFixed(2)
                        : '',
                payment_id: payments.value[0]?.id?.toString() ?? '',
                refunded_at: formatDateInput(new Date()),
                reason: '',
                reference: '',
            };
        }
    },
);

function handleSubmit() {
    isSubmitting.value = true;
    errors.value = {};

    router.post(`/invoices/${props.invoice.id}/refunds`, form.value, {
        onSuccess: () => {
            isSubmitting.value = false;
            toast.success('Refund recorded successfully');
            emit('success');
            emit('update:open', false);
        },
        onError: (pageErrors) => {
            isSubmitting.value = false;
            errors.value = pageErrors as Record<string, string>;
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Record Refund / Issue Credit Note</DialogTitle>
                <DialogDescription>
                    Record a refund for invoice
                    <span class="font-mono"
                        >#{{ invoice.invoice_number ?? invoice.id }}</span
                    >
                    <span
                        v-if="maxRefundAmount > 0"
                        class="ml-1 text-muted-foreground"
                    >
                        · Max refund: {{ fmtCurrency(maxRefundAmount) }}
                    </span>
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-2">
                <div class="grid gap-2">
                    <Label for="refund-amount" required>Refund Amount</Label>
                    <Input
                        id="refund-amount"
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                    />
                    <InputError :message="errors.amount" />
                </div>

                <div v-if="payments.length > 0" class="grid gap-2">
                    <Label for="refund-payment">Related Payment</Label>
                    <Select v-model="form.payment_id">
                        <SelectTrigger id="refund-payment" class="w-full">
                            <SelectValue placeholder="None" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="payment in payments"
                                :key="payment.id"
                                :value="payment.id.toString()"
                            >
                                {{
                                    payment.paid_at
                                        ? new Date(
                                              payment.paid_at,
                                          ).toLocaleDateString()
                                        : 'N/A'
                                }}
                                — {{ fmtCurrency(payment.amount) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.payment_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="refunded_at" required>Refund Date</Label>
                    <Input
                        id="refunded_at"
                        v-model="form.refunded_at"
                        type="date"
                    />
                    <InputError :message="errors.refunded_at" />
                </div>

                <div class="grid gap-2">
                    <Label for="refund-reference">Reference</Label>
                    <Input
                        id="refund-reference"
                        v-model="form.reference"
                        placeholder="Transaction reference..."
                    />
                    <InputError :message="errors.reference" />
                </div>

                <div class="grid gap-2">
                    <Label for="refund-reason">Reason</Label>
                    <Textarea
                        id="refund-reason"
                        v-model="form.reason"
                        placeholder="Reason for refund..."
                        rows="3"
                    />
                    <InputError :message="errors.reason" />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)">
                    Cancel
                </Button>
                <Button :disabled="isSubmitting" @click="handleSubmit">
                    Record Refund
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
