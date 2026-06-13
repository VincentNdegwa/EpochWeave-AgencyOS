<script setup lang="ts">
import {
    PlusIcon,
    Trash2Icon,
    GripVerticalIcon,
    PencilIcon,
    PackageIcon,
} from '@lucide/vue';
import { nanoid } from 'nanoid';
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import { useBuilderDataStore } from '@/stores/builderData';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type {
    BaseBlock,
    PricingTableBlockData,
    PricingLineItem,
} from '@/types/proposal-builder';
import PricingLineItemDialog from './PricingLineItemDialog.vue';

const props = defineProps<{
    data: PricingTableBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const builderDataStore = useBuilderDataStore();

onMounted(async () => {
    await Promise.all([
        builderDataStore.fetchProducts(),
        builderDataStore.fetchUnits(),
    ]);
});

// ── Dialog state ──────────────────────────────────────────────
const dialogOpen = ref(false);
const editingItem = ref<PricingLineItem | null>(null);

const openAddDialog = () => {
    editingItem.value = {
        id: nanoid(8),
        description: '',
        unit: builderDataStore.units[0]?.abbreviation ?? 'hr',
        quantity: 1,
        unit_price: 0,
        subtotal: 0,
        is_optional: false,
        billing_type: 'one_time',
        billing_frequency: 'none',
        product_id: null,
        discount_type: 'none',
        discount_value: 0,
        tax_type: 'none',
        tax_value: 0,
    };
    dialogOpen.value = true;
};

const openEditDialog = (item: PricingLineItem) => {
    editingItem.value = { ...item };
    dialogOpen.value = true;
};

const handleSave = (item: PricingLineItem) => {
    // Add/update item in the catalog
    store.addLineItem(item);

    const exists = props.data.items.some((i) => i.id === item.id);

    if (exists) {
        updateData({
            items: props.data.items.map((i) => (i.id === item.id ? item : i)),
        });
    } else {
        updateData({ items: [...props.data.items, item] });
    }

    dialogOpen.value = false;
    editingItem.value = null;
};

const handleCancel = () => {
    dialogOpen.value = false;
    editingItem.value = null;
};

const removeItem = (id: string) => {
    // Remove from catalog
    store.removeLineItem(id);
    // Remove from block
    updateData({ items: props.data.items.filter((i) => i.id !== id) });
};

const updateData = (changes: Partial<PricingTableBlockData>) => {
    store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

// ── Get items from catalog ───────────────────────────────────────
const catalogItems = computed(() => {
    return store.getPricingTableItems(props.block.id);
});

// ── Computed totals ───────────────────────────────────────────
const fmt = (amount: number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.data.currency || 'USD',
        minimumFractionDigits: 0,
    }).format(amount);

const ensureNumber = (value: number | string | undefined | null, fallback = 0): number => {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : fallback;
    }

    if (typeof value === 'string') {
        const parsed = Number.parseFloat(value);

        return Number.isFinite(parsed) ? parsed : fallback;
    }

    return fallback;
};

const nonOptionalItems = computed(() => catalogItems.value.filter((i) => !i.is_optional));

const resolveDiscountAmount = (item: PricingLineItem): number => {
    if (item.discount_amount !== undefined) {
        return ensureNumber(item.discount_amount);
    }

    const base = ensureNumber(item.subtotal);
    const discountValue = ensureNumber(item.discount_value);

    if (item.discount_type === 'percentage' && discountValue > 0) {
        return Math.round((base * discountValue) / 100);
    }

    if (item.discount_type === 'fixed' && discountValue > 0) {
        return discountValue;
    }

    return 0;
};

const resolveTaxAmount = (item: PricingLineItem): number => {
    if (item.total_tax_amount !== undefined) {
        return ensureNumber(item.total_tax_amount);
    }

    const discount = resolveDiscountAmount(item);
    const base = ensureNumber(item.subtotal) - discount;
    const taxValue = ensureNumber(item.tax_value);

    if (item.tax_type === 'percentage' && taxValue > 0) {
        return Math.round((base * taxValue) / 100);
    }

    if (item.tax_type === 'fixed' && taxValue > 0) {
        return taxValue;
    }

    return 0;
};

const resolveLineTotal = (item: PricingLineItem): number => {
    if (item.total !== undefined) {
        const total = ensureNumber(item.total);

        if (total > 0) {
            return total;
        }
    }

    const base = ensureNumber(item.subtotal);
    const discount = resolveDiscountAmount(item);
    const tax = resolveTaxAmount(item);

    return base - discount + tax;
};

const subtotal = computed(() =>
    nonOptionalItems.value.reduce((sum, item) => sum + ensureNumber(item.subtotal), 0),
);

const tableDiscountAmount = computed(() => {
    if (!props.data.discount) {
        return 0;
    }

    return props.data.discount.type === 'percentage'
        ? Math.round((subtotal.value * props.data.discount.value) / 100)
        : props.data.discount.amount;
});

const lineDiscountTotal = computed(() =>
    nonOptionalItems.value.reduce((sum, item) => sum + resolveDiscountAmount(item), 0),
);

const lineTaxTotal = computed(() =>
    nonOptionalItems.value.reduce((sum, item) => sum + resolveTaxAmount(item), 0),
);

const baseGrandTotal = computed(() =>
    nonOptionalItems.value.reduce((sum, item) => sum + resolveLineTotal(item), 0),
);

const grandTotal = computed(() => Math.max(baseGrandTotal.value - tableDiscountAmount.value, 0));

const gridCols = computed(() => {
    if (props.data.show_quantity_column && props.data.show_unit_column) {
        return 'grid-cols-[1fr_56px_56px_100px]';
    }

    if (props.data.show_quantity_column || props.data.show_unit_column) {
        return 'grid-cols-[1fr_56px_100px]';
    }

    return 'grid-cols-[1fr_100px]';
});
</script>

<template>
    <section class="px-8 py-6">
        <BlockTitle
            v-if="data.title || !isLocked"
            :model-value="data.title"
            placeholder="Pricing"
            :is-locked="isLocked"
            class="mb-5"
            @update:model-value="(v) => updateData({ title: v })"
        />

        <div class="overflow-hidden rounded-lg border border-border">
            <!-- Header -->
            <div
                class="grid border-b border-border bg-muted/50 px-5 py-2.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                :class="[gridCols, !isLocked ? 'pr-20' : '']"
            >
                <span>Description</span>
                <span v-if="data.show_unit_column" class="text-center"
                    >Unit</span
                >
                <span v-if="data.show_quantity_column" class="text-center"
                    >Qty</span
                >
                <span class="text-right">
                    {{ data.show_subtotal_per_line ? 'Subtotal' : 'Price' }}
                </span>
            </div>

            <!-- Rows -->
            <div class="divide-y divide-border">
                <div
                    v-for="item in catalogItems"
                    :key="item.id"
                    class="group/row flex items-center gap-2 px-5 transition hover:bg-muted/20"
                    :class="item.is_optional ? 'opacity-70' : ''"
                >
                    <GripVerticalIcon
                        v-if="!isLocked"
                        class="h-3.5 w-3.5 flex-shrink-0 cursor-grab text-muted-foreground/30 opacity-0 transition group-hover/row:opacity-100"
                    />

                    <div
                        class="grid flex-1 items-center py-3.5"
                        :class="gridCols"
                    >
                        <!-- Name + description + badges -->
                        <div class="min-w-0 pr-4">
                            <p
                                class="truncate text-sm font-medium text-foreground"
                            >
                                {{ item.description || 'Untitled item' }}
                            </p>
                            <div
                                v-if="
                                    item.is_optional ||
                                    item.billing_type === 'recurring'
                                "
                                class="mt-0.5 flex flex-wrap items-center gap-1.5"
                            >
                                <span
                                    v-if="item.is_optional"
                                    class="rounded-full bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground"
                                    >Optional</span
                                >
                                <span
                                    v-if="item.billing_type === 'recurring'"
                                    class="rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] text-blue-600 dark:bg-blue-950/60 dark:text-blue-400"
                                    >Recurring ·
                                    {{ item.billing_frequency }}</span
                                >
                            </div>
                            <div
                                v-if="resolveDiscountAmount(item) > 0 || resolveTaxAmount(item) > 0"
                                class="mt-1 flex flex-wrap gap-1.5 text-[11px]"
                            >
                                <span
                                    v-if="resolveDiscountAmount(item) > 0"
                                    class="rounded-full bg-rose-50 px-1.5 py-0.5 font-medium text-rose-500 dark:bg-rose-500/10 dark:text-rose-300"
                                >
                                    Discount −{{ fmt(resolveDiscountAmount(item)) }}
                                </span>
                                <span
                                    v-if="resolveTaxAmount(item) > 0"
                                    class="rounded-full bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"
                                >
                                    Tax +{{ fmt(resolveTaxAmount(item)) }}
                                </span>
                            </div>
                        </div>

                        <span
                            v-if="data.show_unit_column"
                            class="text-center text-sm text-muted-foreground"
                            >{{ item.unit }}</span
                        >
                        <span
                            v-if="data.show_quantity_column"
                            class="text-center text-sm text-foreground"
                            >{{ item.quantity }}</span
                        >
                        <span
                            class="text-right text-sm font-medium text-foreground"
                        >
                            {{
                                fmt(
                                    data.show_subtotal_per_line
                                        ? resolveLineTotal(item)
                                        : ensureNumber(item.unit_price),
                                )
                            }}
                        </span>
                    </div>

                    <!-- Edit + Delete -->
                    <div
                        v-if="!isLocked"
                        class="flex flex-shrink-0 items-center gap-1 opacity-0 transition group-hover/row:opacity-100"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-7 w-7 text-muted-foreground hover:text-foreground"
                            @click="openEditDialog(item)"
                        >
                            <PencilIcon class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-7 w-7 text-muted-foreground hover:text-destructive"
                            @click="removeItem(item.id)"
                        >
                            <Trash2Icon class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>

                <!-- Empty -->
                <div
                    v-if="data.items.length === 0"
                    class="flex flex-col items-center gap-2 py-10 text-center"
                >
                    <PackageIcon class="h-6 w-6 text-muted-foreground/30" />
                    <p class="text-sm font-medium text-foreground">
                        No line items yet
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{
                            isLocked
                                ? 'No items have been added.'
                                : 'Click below to add your first item.'
                        }}
                    </p>
                </div>
            </div>

            <!-- Add row -->
            <button
                v-if="!isLocked"
                type="button"
                class="flex w-full items-center gap-2 border-t border-border px-5 py-3 text-xs text-muted-foreground transition hover:bg-muted/40 hover:text-foreground"
                @click="openAddDialog"
            >
                <PlusIcon class="h-3.5 w-3.5" />
                Add line item
            </button>
        </div>

        <!-- Totals -->
        <div class="mt-5 flex justify-end">
            <div class="w-72 space-y-2 text-sm">
                <div
                    v-if="data.show_subtotal_row"
                    class="flex justify-between text-muted-foreground"
                >
                    <span>Subtotal</span><span>{{ fmt(subtotal) }}</span>
                </div>
                <div
                    v-if="lineDiscountTotal > 0"
                    class="flex justify-between text-muted-foreground"
                >
                    <span>Discount</span>
                    <span class="text-red-500 dark:text-red-400"
                        >−{{ fmt(lineDiscountTotal) }}</span
                    >
                </div>
                <div
                    v-if="data.discount && tableDiscountAmount"
                    class="flex justify-between text-muted-foreground"
                >
                    <span>{{ data.discount.label || 'Discount' }}</span>
                    <span class="text-red-500 dark:text-red-400"
                        >−{{ fmt(tableDiscountAmount) }}</span
                    >
                </div>
                <div
                    v-if="data.show_tax_row && lineTaxTotal > 0"
                    class="flex justify-between text-muted-foreground"
                >
                    <span>{{ data.tax_label || 'Taxes' }}</span>
                    <span>{{ fmt(lineTaxTotal) }}</span>
                </div>
                <div
                    v-if="data.show_total_row"
                    class="flex justify-between border-t border-border pt-2.5 text-base font-semibold text-foreground"
                >
                    <span>Total</span><span>{{ fmt(grandTotal) }}</span>
                </div>
                <p
                    v-if="data.footer_note"
                    class="pt-1 text-[11px] text-muted-foreground"
                >
                    {{ data.footer_note }}
                </p>
            </div>
        </div>

        <!-- Dialog -->
        <PricingLineItemDialog
            v-if="editingItem"
            :open="dialogOpen"
            :item="editingItem"
            :currency="data.currency"
            :products="builderDataStore.products"
            :units="builderDataStore.units"
            @save="handleSave"
            @cancel="handleCancel"
        />
    </section>
</template>
