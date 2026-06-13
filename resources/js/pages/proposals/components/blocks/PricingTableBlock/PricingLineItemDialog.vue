<script setup lang="ts">
import {
    PackageIcon,
    TypeIcon,
    CoinsIcon,
    TagIcon,
    ToggleLeftIcon,
} from '@lucide/vue';
import type { AcceptableValue } from 'reka-ui';
import { reactive, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useBillingFrequencies } from '@/composables/useEnums';
import type { PricingLineItem } from '@/types/proposal-builder';

// ── Props / emits ─────────────────────────────────────────────
const props = defineProps<{
    open: boolean;
    item: PricingLineItem;
    currency: string;
    products: Array<{
        id: number;
        name: string;
        unit?: { abbreviation: string };
        unit_price: number;
        billing_type: PricingLineItem['billing_type'];
        billing_frequency: PricingLineItem['billing_frequency'];
    }>;
    units: Array<{ id: number; abbreviation: string; name?: string }>;
}>();

const emit = defineEmits<{
    save: [item: PricingLineItem];
    cancel: [];
}>();

// ── Local form state ──────────────────────────────────────────
// We work on a local copy so cancel truly discards changes
const form = reactive<
    PricingLineItem & {
        description: string | null;
        discount_type: 'none' | 'percentage' | 'fixed';
        discount_value: number;
        tax_type: 'none' | 'percentage' | 'fixed';
        tax_value: number;
    }
>({
    ...props.item,
    description: (props.item as any).description ?? null,
    discount_type: (props.item as any).discount_type ?? 'none',
    discount_value: (props.item as any).discount_value ?? 0,
    tax_type: (props.item as any).tax_type ?? 'none',
    tax_value: (props.item as any).tax_value ?? 0,
    billing_frequency: props.item.billing_frequency ?? 'none',
    discount_amount: (props.item as any).discount_amount ?? 0,
    total_tax_amount: (props.item as any).total_tax_amount ?? 0,
    total: (props.item as any).total ?? props.item.subtotal ?? 0,
});

// Sync form when dialog opens with a new item
watch(
    () => props.item,
    (newItem) => {
        Object.assign(form, {
            ...newItem,
            description: (newItem as any).description ?? null,
            discount_type: (newItem as any).discount_type ?? 'none',
            discount_value: (newItem as any).discount_value ?? 0,
            tax_type: (newItem as any).tax_type ?? 'none',
            tax_value: (newItem as any).tax_value ?? 0,
            billing_frequency: newItem.billing_frequency ?? 'none',
            discount_amount: (newItem as any).discount_amount ?? 0,
            total_tax_amount: (newItem as any).total_tax_amount ?? 0,
            total: (newItem as any).total ?? newItem.subtotal ?? 0,
        });
    },
    { immediate: true },
);

// ── Product catalog selection ─────────────────────────────────
const { values: billingFrequencyOptions } = useBillingFrequencies();

const handleProductSelect = (rawValue: AcceptableValue) => {
    if (typeof rawValue === 'object') {
        return;
    }

    const value = String(rawValue);

    if (value === 'custom') {
        form.product_id = null;

        return;
    }

    const productId = Number(value);
    const product = props.products.find((p) => p.id === productId);

    if (product) {
        form.product_id = productId;
        form.description = product.name;
        form.unit = product.unit?.abbreviation ?? form.unit;
        form.unit_price = product.unit_price;
        form.billing_type =
            product.billing_type as PricingLineItem['billing_type'];
        form.billing_frequency = product.billing_frequency;
    }
};

watch(
    () => form.billing_type,
    (type) => {
        if (type === 'one_time') {
            form.billing_frequency = 'none';
        } else if (form.billing_frequency === 'none') {
            form.billing_frequency = 'monthly';
        }
    },
);

// ── Live computed financials ──────────────────────────────────
const lineSubtotal = computed(() => form.quantity * form.unit_price);

const discountAmount = computed(() => {
    if (form.discount_type === 'none') {
        return 0;
    }

    if (form.discount_type === 'percentage') {
        return Math.round(
            (lineSubtotal.value * form.discount_value) / 100,
        );
    }

    return form.discount_value;
});

const afterDiscount = computed(() => lineSubtotal.value - discountAmount.value);

const taxAmount = computed(() => {
    if (form.tax_type === 'none') {
        return 0;
    }

    if (form.tax_type === 'percentage') {
        return Math.round((afterDiscount.value * form.tax_value) / 100);
    }

    return form.tax_value;
});

const lineTotal = computed(() => afterDiscount.value + taxAmount.value);

watch(
    [lineSubtotal, discountAmount, taxAmount, lineTotal],
    ([base, discount, tax, total]) => {
        form.subtotal = base;
        form.discount_amount = discount;
        form.total_tax_amount = tax;
        form.total = total;
    },
    { immediate: true },
);

// ── Currency formatter ────────────────────────────────────────
const fmt = (amount: number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency || 'USD',
        minimumFractionDigits: 0,
    }).format(amount);

// ── Save ──────────────────────────────────────────────────────
const handleSave = () => {
    emit('save', { ...form });
};
</script>

<template>
    <Dialog :open="open" @update:open="(v) => !v && emit('cancel')">
        <DialogContent class="sm:max-w-xl md:max-w-2xl">
            <DialogHeader>
                <DialogTitle class="text-base font-semibold text-foreground">
                    {{ item.description ? 'Edit line item' : 'Add line item' }}
                </DialogTitle>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="description" required>Name</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="e.g. Website Design, Logo Package…"
                            required
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="product">From catalog</Label>
                        <Select
                            :model-value="form.product_id ? String(form.product_id) : 'custom'"
                            @update:model-value="handleProductSelect"
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select from catalog or add custom…" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="custom">
                                    Custom item (no catalog link)
                                </SelectItem>
                                <div v-if="products.length" class="my-1 border-t border-border" />
                                <SelectItem
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="String(product.id)"
                                >
                                    {{ product.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="unit">Unit</Label>
                        <Select v-model="form.unit">
                            <SelectTrigger class="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="unit in units"
                                    :key="unit.id"
                                    :value="unit.abbreviation"
                                >
                                    {{ unit.abbreviation }}
                                    <span v-if="unit.name" class="ml-1 text-muted-foreground">
                                        · {{ unit.name }}
                                    </span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="quantity">Quantity</Label>
                        <Input
                            id="quantity"
                            v-model.number="form.quantity"
                            type="number"
                            min="0"
                            step="0.5"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="unit_price">Unit price</Label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground">
                                {{ currency }}
                            </div>
                            <Input
                                id="unit_price"
                                v-model.number="form.unit_price"
                                type="number"
                                min="0"
                                class="pl-12"
                            />
                        </div>
                    </div>

                    
                    <div class="grid gap-2">
                        <Label>Billing type</Label>
                        <div class="grid grid-cols-2 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
                            <button
                                type="button"
                                class="rounded py-1.5 text-xs font-medium transition"
                                :class="form.billing_type === 'one_time' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                @click="form.billing_type = 'one_time'"
                            >
                                One-time
                            </button>
                            <button
                                type="button"
                                class="rounded py-1.5 text-xs font-medium transition"
                                :class="form.billing_type === 'recurring' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                @click="form.billing_type = 'recurring'"
                            >
                                Recurring
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Billing frequency</Label>
                        <Select v-model="form.billing_frequency" :disabled="form.billing_type === 'one_time'">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select frequency" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in billingFrequencyOptions"
                                    :key="option.value"
                                    :value="option.value"
                                    :disabled="option.value !== 'none' && form.billing_type === 'one_time'"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label>Item discount</Label>
                        <div class="grid grid-cols-3 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
                            <button
                                v-for="opt in [
                                    { value: 'none', label: 'None' },
                                    { value: 'percentage', label: 'Percent %' },
                                    { value: 'fixed', label: `Fixed ${currency}` },
                                ]"
                                :key="opt.value"
                                type="button"
                                class="rounded py-1.5 text-xs font-medium transition"
                                :class="form.discount_type === opt.value ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                @click="form.discount_type = opt.value as typeof form.discount_type"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                        <div
                            v-if="form.discount_type !== 'none'"
                            class="flex items-center gap-3"
                        >
                            <div class="relative flex-1">
                                <Input
                                    v-model.number="form.discount_value"
                                    type="number"
                                    min="0"
                                    :max="form.discount_type === 'percentage' ? 100 : undefined"
                                    class="pr-10"
                                    placeholder="0"
                                />
                                <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-xs text-muted-foreground">
                                    {{ form.discount_type === 'percentage' ? '%' : currency }}
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-muted-foreground">Discount</p>
                                <p class="text-sm font-semibold text-red-500 dark:text-red-400">
                                    −{{ fmt(discountAmount) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>Item tax</Label>
                        <div class="grid grid-cols-3 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
                            <button
                                v-for="opt in [
                                    { value: 'none', label: 'None' },
                                    { value: 'percentage', label: 'Percent %' },
                                    { value: 'fixed', label: `Fixed ${currency}` },
                                ]"
                                :key="opt.value"
                                type="button"
                                class="rounded py-1.5 text-xs font-medium transition"
                                :class="form.tax_type === opt.value ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                @click="form.tax_type = opt.value as typeof form.tax_type"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                        <div
                            v-if="form.tax_type !== 'none'"
                            class="flex items-center gap-3"
                        >
                            <div class="relative flex-1">
                                <Input
                                    v-model.number="form.tax_value"
                                    type="number"
                                    min="0"
                                    class="pr-10"
                                    placeholder="0"
                                />
                                <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-xs text-muted-foreground">
                                    {{ form.tax_type === 'percentage' ? '%' : currency }}
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-muted-foreground">Tax</p>
                                <p class="text-sm font-semibold text-foreground">
                                    +{{ fmt(taxAmount) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <div class="flex items-center gap-2">
                            <Switch v-model:checked="form.is_optional" />
                            <Label for="is_optional">Optional item</Label>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Client can choose to include or exclude this item
                        </p>
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label>Line summary</Label>
                        <div class="overflow-hidden rounded-lg border border-border">
                            <div class="divide-y divide-border text-sm">
                                <div class="flex justify-between px-4 py-2.5 text-muted-foreground">
                                    <span>Subtotal</span>
                                    <span>{{ fmt(lineSubtotal) }}</span>
                                </div>
                                <div
                                    v-if="form.discount_type !== 'none'"
                                    class="flex justify-between px-4 py-2.5 text-muted-foreground"
                                >
                                    <span>Discount</span>
                                    <span class="text-red-500 dark:text-red-400">−{{ fmt(discountAmount) }}</span>
                                </div>
                                <div
                                    v-if="form.tax_type !== 'none'"
                                    class="flex justify-between px-4 py-2.5 text-muted-foreground"
                                >
                                    <span>Tax</span>
                                    <span>+{{ fmt(taxAmount) }}</span>
                                </div>
                                <div class="flex justify-between bg-muted/30 px-4 py-3 font-semibold text-foreground">
                                    <span>Line total</span>
                                    <span>{{ fmt(lineTotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('cancel')">Cancel</Button>
                <Button :disabled="!form.description.trim()" @click="handleSave">
                    {{ item.description ? 'Update' : 'Add' }} line item
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
