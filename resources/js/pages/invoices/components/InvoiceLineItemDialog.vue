<script setup lang="ts">
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
import { Textarea } from '@/components/ui/textarea';
import { useCurrency } from '@/composables/useCurrency';

interface LineItemForm {
    id: string;
    product_id: number | null;
    item_name: string;
    description: string;
    unit_label: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
    discount_type: 'none' | 'percentage' | 'fixed';
    discount_value: number;
    discount_amount: number;
    tax_type: 'none' | 'percentage' | 'fixed';
    tax_value: number;
    total_tax_amount: number;
    total: number;
}

const props = defineProps<{
    open: boolean;
    item: Partial<LineItemForm>;
    products: Array<{
        id: number;
        name: string;
        description: string | null;
        unit_price: number;
        unit?: { abbreviation: string } | null;
    }>;
}>();

const emit = defineEmits<{
    save: [item: LineItemForm];
    cancel: [];
}>();

const { format: fmt } = useCurrency();

const form = reactive<LineItemForm>({
    id: props.item.id ?? crypto.randomUUID(),
    product_id: props.item.product_id ?? null,
    item_name: props.item.item_name ?? '',
    description: props.item.description ?? '',
    unit_label: props.item.unit_label ?? 'Pcs',
    quantity: props.item.quantity ?? 1,
    unit_price: props.item.unit_price ?? 0,
    subtotal: props.item.subtotal ?? 0,
    discount_type: props.item.discount_type ?? 'none',
    discount_value: props.item.discount_value ?? 0,
    discount_amount: props.item.discount_amount ?? 0,
    tax_type: props.item.tax_type ?? 'none',
    tax_value: props.item.tax_value ?? 0,
    total_tax_amount: props.item.total_tax_amount ?? 0,
    total: props.item.total ?? 0,
});

watch(
    () => props.item,
    (newItem) => {
        Object.assign(form, {
            id: newItem.id ?? crypto.randomUUID(),
            product_id: newItem.product_id ?? null,
            item_name: newItem.item_name ?? '',
            description: newItem.description ?? '',
            unit_label: newItem.unit_label ?? 'Pcs',
            quantity: newItem.quantity ?? 1,
            unit_price: newItem.unit_price ?? 0,
            subtotal: newItem.subtotal ?? 0,
            discount_type: newItem.discount_type ?? 'none',
            discount_value: newItem.discount_value ?? 0,
            discount_amount: newItem.discount_amount ?? 0,
            tax_type: newItem.tax_type ?? 'none',
            tax_value: newItem.tax_value ?? 0,
            total_tax_amount: newItem.total_tax_amount ?? 0,
            total: newItem.total ?? 0,
        });
    },
    { immediate: true },
);

const handleProductSelect = (value: string) => {
    if (value === 'custom') {
        form.product_id = null;

        return;
    }

    const productId = Number(value);
    const product = props.products.find((p) => p.id === productId);

    if (product) {
        form.product_id = productId;
        form.item_name = product.name;
        form.description = product.description ?? '';
        form.unit_label = product.unit?.abbreviation ?? form.unit_label;
        form.unit_price = product.unit_price;
    }
};

const lineSubtotal = computed(() => form.quantity * form.unit_price);

const discountAmount = computed(() => {
    if (form.discount_type === 'none') {
return 0;
}

    if (form.discount_type === 'percentage') {
        return Math.round((lineSubtotal.value * form.discount_value) / 100 * 100) / 100;
    }

    return form.discount_value;
});

const afterDiscount = computed(() => lineSubtotal.value - discountAmount.value);

const taxAmount = computed(() => {
    if (form.tax_type === 'none') {
return 0;
}

    if (form.tax_type === 'percentage') {
        return Math.round((afterDiscount.value * form.tax_value) / 100 * 100) / 100;
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

const handleSave = () => {
    emit('save', { ...form });
};
</script>

<template>
    <Dialog :open="open" @update:open="(v) => !v && emit('cancel')">
        <DialogContent class="sm:max-w-xl max-h-[90dvh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="text-base font-semibold text-foreground">
                    {{ item.item_name ? 'Edit line item' : 'Add line item' }}
                </DialogTitle>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="product_id">Product catalog</Label>
                        <Select :model-value="form.product_id?.toString() ?? 'custom'" @update:model-value="handleProductSelect">
                            <SelectTrigger class="w-full" >
                                <SelectValue placeholder="Select a product or enter custom item" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="custom">Custom item</SelectItem>
                                <SelectItem
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id.toString()"
                                >
                                    {{ product.name }} — {{ fmt(product.unit_price) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="item_name" required>Item name</Label>
                        <Input
                            id="item_name"
                            v-model="form.item_name"
                            placeholder="Service or product name"
                            required
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="unit_label">Unit</Label>
                        <Input
                            id="unit_label"
                            v-model="form.unit_label"
                            placeholder="Pcs, Hr, etc."
                        />
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
                        <Input
                            id="unit_price"
                            v-model.number="form.unit_price"
                            type="number"
                            min="0"
                            step="0.01"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label>Discount</Label>
                        <div class="grid grid-cols-3 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
                            <button
                                v-for="opt in [
                                    { value: 'none', label: 'None' },
                                    { value: 'percentage', label: 'Percent %' },
                                    { value: 'fixed', label: 'Fixed' },
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
                        <div v-if="form.discount_type !== 'none'" class="flex items-center gap-3">
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
                                    {{ form.discount_type === 'percentage' ? '%' : 'Amt' }}
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
                        <Label>Tax</Label>
                        <div class="grid grid-cols-3 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
                            <button
                                v-for="opt in [
                                    { value: 'none', label: 'None' },
                                    { value: 'percentage', label: 'Percent %' },
                                    { value: 'fixed', label: 'Fixed' },
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
                        <div v-if="form.tax_type !== 'none'" class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <Input
                                    v-model.number="form.tax_value"
                                    type="number"
                                    min="0"
                                    class="pr-10"
                                    placeholder="0"
                                />
                                <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-xs text-muted-foreground">
                                    {{ form.tax_type === 'percentage' ? '%' : 'Amt' }}
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
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Item description"
                            rows="2"
                        />
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
                <Button :disabled="!form.item_name.trim()" @click="handleSave">
                    {{ item.item_name ? 'Update' : 'Add' }} line item
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
