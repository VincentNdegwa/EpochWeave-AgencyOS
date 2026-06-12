<script setup lang="ts">
import { TableIcon, CoinsIcon, ToggleLeftIcon, TagIcon } from '@lucide/vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type {
    PricingTableBlockData,
    PricingDiscount,
} from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<PricingTableBlockData>(
    props.blockId,
);

const updateDiscount = (changes: Partial<PricingDiscount>) => {
    if (!data.value) {
        return;
    }

    const current = data.value.discount ?? {
        type: 'percentage',
        value: 0,
        label: 'Discount',
        amount: 0,
    };
    updateData({ discount: { ...current, ...changes } });
};

const currencies = ['KES', 'NGN', 'GHS', 'USD', 'GBP', 'EUR'] as const;
</script>

<template>
    <div v-if="data" class="flex flex-col gap-0 text-sm">
        <!-- SECTION 1 — CURRENCY -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <CoinsIcon class="h-3 w-3" />
                Currency
            </p>
            <div class="grid grid-cols-3 gap-1">
                <button
                    v-for="c in currencies"
                    :key="c"
                    type="button"
                    class="rounded-md border py-1.5 text-center text-xs font-medium transition"
                    :class="
                        data.currency === c
                            ? 'border-primary bg-primary/5 text-primary'
                            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'
                    "
                    @click="updateData({ currency: c })"
                >
                    {{ c }}
                </button>
            </div>
        </div>

        <!-- SECTION 2 — COLUMNS -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <TableIcon class="h-3 w-3" />
                Columns
            </p>

            <div
                class="space-y-0 divide-y divide-border overflow-hidden rounded-lg border border-border"
            >
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Show unit column
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            hr, day, pkg, item…
                        </p>
                    </div>
                    <Switch
                        :model-value="data.show_unit_column"
                        @update:model-value="
                            (v: boolean) => updateData({ show_unit_column: v })
                        "
                    />
                </label>
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Show quantity column
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            Number of units per item
                        </p>
                    </div>
                    <Switch
                        :model-value="data.show_quantity_column"
                        @update:model-value="
                            (v: boolean) =>
                                updateData({ show_quantity_column: v })
                        "
                    />
                </label>
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Show subtotal per line
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            Qty × unit price per row
                        </p>
                    </div>
                    <Switch
                        :model-value="data.show_subtotal_per_line"
                        @update:model-value="
                            (v: boolean) =>
                                updateData({ show_subtotal_per_line: v })
                        "
                    />
                </label>
            </div>
        </div>

        <!-- SECTION 3 — TOTALS -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <ToggleLeftIcon class="h-3 w-3" />
                Totals
            </p>

            <div
                class="mb-3 space-y-0 divide-y divide-border overflow-hidden rounded-lg border border-border"
            >
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <p class="text-xs font-medium text-foreground">
                        Show subtotal row
                    </p>
                    <Switch
                        :model-value="data.show_subtotal_row"
                        @update:model-value="
                            (v: boolean) => updateData({ show_subtotal_row: v })
                        "
                    />
                </label>
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <p class="text-xs font-medium text-foreground">
                        Show grand total row
                    </p>
                    <Switch
                        :model-value="data.show_total_row"
                        @update:model-value="
                            (v: boolean) => updateData({ show_total_row: v })
                        "
                    />
                </label>
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Show tax row
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            Adds a tax line above total
                        </p>
                    </div>
                    <Switch
                        :model-value="data.show_tax_row"
                        @update:model-value="
                            (v: boolean) => updateData({ show_tax_row: v })
                        "
                    />
                </label>
            </div>

            <!-- Tax config -->
            <div v-if="data.show_tax_row" class="space-y-2">
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground"
                        >Tax label</Label
                    >
                    <Input
                        :model-value="data.tax_label"
                        placeholder="VAT, GST, Tax…"
                        class="h-8 text-xs"
                        @update:model-value="
                            (v) => updateData({ tax_label: String(v ?? 'Tax') })
                        "
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground"
                        >Tax rate (%)</Label
                    >
                    <div class="relative">
                        <Input
                            type="number"
                            min="0"
                            max="100"
                            :model-value="data.tax_rate"
                            class="h-8 pr-8 text-xs"
                            @update:model-value="
                                (v) => updateData({ tax_rate: Number(v ?? 0) })
                            "
                        />
                        <span
                            class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-xs text-muted-foreground"
                            >%</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4 — DISCOUNT -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <TagIcon class="h-3 w-3" />
                Discount
            </p>

            <label
                class="flex cursor-pointer items-center justify-between rounded-lg border px-3 py-2.5 transition-colors hover:bg-muted/50"
                :class="
                    data.discount
                        ? 'border-primary bg-primary/5'
                        : 'border-border'
                "
            >
                <div>
                    <p class="text-xs font-medium text-foreground">
                        Apply discount
                    </p>
                    <p class="text-[11px] text-muted-foreground">
                        Show a discount line in totals
                    </p>
                </div>
                <Switch
                    :model-value="!!data.discount"
                    @update:model-value="
                        (v: boolean) =>
                            updateData({
                                discount: v
                                    ? {
                                          type: 'percentage',
                                          value: 10,
                                          label: 'Discount',
                                          amount: 0,
                                      }
                                    : null,
                            })
                    "
                />
            </label>

            <div v-if="data.discount" class="mt-3 space-y-2">
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground">Label</Label>
                    <Input
                        :model-value="data.discount.label"
                        placeholder="Early payment discount…"
                        class="h-8 text-xs"
                        @update:model-value="
                            (v) =>
                                updateDiscount({
                                    label: String(v ?? 'Discount'),
                                })
                        "
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground">Type</Label>
                    <div
                        class="grid grid-cols-2 gap-1 rounded-md border border-border bg-muted/40 p-0.5"
                    >
                        <button
                            type="button"
                            class="rounded py-1.5 text-xs font-medium transition"
                            :class="
                                data.discount.type === 'percentage'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground'
                            "
                            @click="updateDiscount({ type: 'percentage' })"
                        >
                            Percentage %
                        </button>
                        <button
                            type="button"
                            class="rounded py-1.5 text-xs font-medium transition"
                            :class="
                                data.discount.type === 'fixed'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground'
                            "
                            @click="updateDiscount({ type: 'fixed' })"
                        >
                            Fixed amount
                        </button>
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground">Value</Label>
                    <div class="relative">
                        <Input
                            type="number"
                            min="0"
                            :model-value="data.discount.value"
                            class="h-8 pr-8 text-xs"
                            @update:model-value="
                                (v) => updateDiscount({ value: Number(v ?? 0) })
                            "
                        />
                        <span
                            class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-xs text-muted-foreground"
                        >
                            {{
                                data.discount.type === 'percentage'
                                    ? '%'
                                    : data.currency
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 5 — FOOTER NOTE -->
        <div class="py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                Footer note
            </p>
            <Textarea
                :model-value="data.footer_note ?? ''"
                placeholder="e.g. Prices valid for 30 days. Payment terms: 50% upfront."
                rows="2"
                class="resize-none text-xs"
                @update:model-value="
                    (v) => updateData({ footer_note: v ? String(v) : null })
                "
            />
        </div>
    </div>
    <p v-else class="py-3 text-sm text-muted-foreground">
        Block data unavailable.
    </p>
</template>
