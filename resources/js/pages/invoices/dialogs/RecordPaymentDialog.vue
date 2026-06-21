<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { toast } from 'vue-sonner';
import PaymentController from '@/actions/App/Http/Controllers/PaymentController';
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

const remainingAmount = computed(() => {
    const total = props.invoice.grand_total ?? 0;
    const paid = props.invoice.amount_paid ?? 0;

    return Math.max(total - paid, 0);
});

const form = ref({
    amount: '',
    method: 'bank_transfer',
    paid_at: formatDateInput(new Date()),
    reference: '',
    notes: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            form.value = {
                amount: remainingAmount.value.toFixed(2),
                method: 'bank_transfer',
                paid_at: formatDateInput(new Date()),
                reference: '',
                notes: '',
            };
        }
    },
);

function handleSubmit() {
    isSubmitting.value = true;
    errors.value = {};

    router.post(
        PaymentController.store({ invoice: props.invoice.id }).url,
        form.value,
        {
            onSuccess: () => {
                isSubmitting.value = false;
                toast.success('Payment recorded successfully');
                emit('success');
                emit('update:open', false);
            },
            onError: (pageErrors) => {
                isSubmitting.value = false;
                errors.value = pageErrors as Record<string, string>;
            },
        },
    );
}
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Record Payment</DialogTitle>
                <DialogDescription>
                    Record a payment for invoice
                    <span class="font-mono"
                        >#{{ invoice.invoice_number ?? invoice.id }}</span
                    >
                    <span
                        v-if="remainingAmount > 0"
                        class="ml-1 text-muted-foreground"
                    >
                        · Remaining: {{ fmtCurrency(remainingAmount) }}
                    </span>
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-2">
                <div class="grid gap-2">
                    <Label for="amount" required>Amount</Label>
                    <Input
                        id="amount"
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                    />
                    <InputError :message="errors.amount" />
                </div>

                <div class="grid gap-2">
                    <Label for="method" required>Payment Method</Label>
                    <Select v-model="form.method">
                        <SelectTrigger>
                            <SelectValue placeholder="Select method" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="bank_transfer"
                                >Bank Transfer</SelectItem
                            >
                            <SelectItem value="credit_card"
                                >Credit Card</SelectItem
                            >
                            <SelectItem value="cash">Cash</SelectItem>
                            <SelectItem value="check">Check</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.method" />
                </div>

                <div class="grid gap-2">
                    <Label for="paid_at" required>Paid Date</Label>
                    <Input id="paid_at" v-model="form.paid_at" type="date" />
                    <InputError :message="errors.paid_at" />
                </div>

                <div class="grid gap-2">
                    <Label for="reference">Reference</Label>
                    <Input
                        id="reference"
                        v-model="form.reference"
                        placeholder="Transaction reference..."
                    />
                    <InputError :message="errors.reference" />
                </div>

                <div class="grid gap-2">
                    <Label for="notes">Notes</Label>
                    <Textarea
                        id="notes"
                        v-model="form.notes"
                        placeholder="Additional notes..."
                        rows="3"
                    />
                    <InputError :message="errors.notes" />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)">
                    Cancel
                </Button>
                <Button :disabled="isSubmitting" @click="handleSubmit">
                    Record Payment
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
