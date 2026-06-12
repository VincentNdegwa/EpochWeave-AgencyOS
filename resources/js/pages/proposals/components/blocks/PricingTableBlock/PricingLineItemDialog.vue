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
  save:   [item: PricingLineItem];
  cancel: [];
}>();

// ── Local form state ──────────────────────────────────────────
// We work on a local copy so cancel truly discards changes
const form = reactive<PricingLineItem & {
  item_description: string | null;
  item_discount_type: 'none' | 'percentage' | 'fixed';
  item_discount_value: number;
  item_tax_type: 'none' | 'percentage' | 'fixed';
  item_tax_value: number;
}>({
  ...props.item,
  item_description:    (props.item as any).item_description    ?? null,
  item_discount_type:  (props.item as any).item_discount_type  ?? 'none',
  item_discount_value: (props.item as any).item_discount_value ?? 0,
  item_tax_type:       (props.item as any).item_tax_type       ?? 'none',
  item_tax_value:      (props.item as any).item_tax_value      ?? 0,
  billing_frequency:   props.item.billing_frequency ?? 'none',
});

// Sync form when dialog opens with a new item
watch(() => props.item, (newItem) => {
  Object.assign(form, {
    ...newItem,
    item_description:    (newItem as any).item_description    ?? null,
    item_discount_type:  (newItem as any).item_discount_type  ?? 'none',
    item_discount_value: (newItem as any).item_discount_value ?? 0,
    item_tax_type:       (newItem as any).item_tax_type       ?? 'none',
    item_tax_value:      (newItem as any).item_tax_value      ?? 0,
    billing_frequency:   newItem.billing_frequency ?? 'none',
  });
}, { immediate: true });

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
  const product   = props.products.find((p) => p.id === productId);

  if (product) {
    form.product_id   = productId;
    form.description  = product.name;
    form.unit         = product.unit?.abbreviation ?? form.unit;
    form.unit_price   = product.unit_price;
    form.billing_type = product.billing_type as PricingLineItem['billing_type'];
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
  if (form.item_discount_type === 'none') {
return 0;
}

  if (form.item_discount_type === 'percentage') {
return Math.round(lineSubtotal.value * form.item_discount_value / 100);
}

  return form.item_discount_value;
});

const afterDiscount = computed(() => lineSubtotal.value - discountAmount.value);

const taxAmount = computed(() => {
  if (form.item_tax_type === 'none') {
return 0;
}

  if (form.item_tax_type === 'percentage') {
return Math.round(afterDiscount.value * form.item_tax_value / 100);
}

  return form.item_tax_value;
});

const lineTotal = computed(() => afterDiscount.value + taxAmount.value);

// Keep subtotal in sync so the table display is accurate
watch(lineTotal, (v) => {
 form.subtotal = v; 
});

// ── Currency formatter ────────────────────────────────────────
const fmt = (amount: number) =>
  new Intl.NumberFormat('en-US', {
    style:                 'currency',
    currency:              props.currency || 'USD',
    minimumFractionDigits: 0,
  }).format(amount);

// ── Save ──────────────────────────────────────────────────────
const handleSave = () => {
  emit('save', { ...form });
};

</script>

<template>
  <Dialog :open="open" @update:open="(v) => !v && emit('cancel')">
    <DialogContent class="max-w-lg gap-0 overflow-hidden p-0">

      <!-- Header -->
      <DialogHeader class="border-b border-border px-6 py-4">
        <DialogTitle class="text-base font-semibold text-foreground">
          {{ item.description ? 'Edit line item' : 'Add line item' }}
        </DialogTitle>
      </DialogHeader>

      <!-- Body -->
      <div class="flex flex-col gap-0 overflow-y-auto max-h-[70vh]">

        <!-- ══════ SECTION 1 — PRODUCT ══════ -->
        <div class="px-6 py-4 border-b border-border">
          <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                    tracking-wider text-muted-foreground">
            <PackageIcon class="h-3 w-3" />
            Product
          </p>

          <div class="grid gap-3">
            <!-- Catalog picker -->
            <div class="grid gap-1.5">
              <Label class="text-xs text-muted-foreground">From catalog <span class="opacity-50">(optional)</span></Label>
              <Select
                :model-value="form.product_id ? String(form.product_id) : 'custom'"
                @update:model-value="handleProductSelect"
              >
                <SelectTrigger class="h-9">
                  <SelectValue placeholder="Select from catalog or add custom…" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="custom">
                    <span class="text-muted-foreground">Custom item (no catalog link)</span>
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
              <p class="text-[11px] text-muted-foreground">
                Selecting a product pre-fills the fields below. You can still edit them freely.
              </p>
            </div>
          </div>
        </div>

        <!-- ══════ SECTION 2 — DESCRIPTION ══════ -->
        <div class="px-6 py-4 border-b border-border">
          <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                    tracking-wider text-muted-foreground">
            <TypeIcon class="h-3 w-3" />
            Item details
          </p>

          <div class="grid gap-3">
            <!-- Name -->
            <div class="grid gap-1.5">
              <Label class="text-xs text-muted-foreground">Name <span class="text-destructive">*</span></Label>
              <Input
                v-model="form.description"
                placeholder="e.g. Website Design, Logo Package…"
                class="h-9"
              />
            </div>

            <!-- Description -->
            <div class="grid gap-1.5">
              <Label class="text-xs text-muted-foreground">
                Description <span class="opacity-50">(optional)</span>
              </Label>
              <Textarea
                :model-value="form.item_description ?? ''"
                placeholder="Brief description shown below the name on the proposal…"
                rows="2"
                class="resize-none text-sm"
                @update:model-value="(v) => form.item_description = v ? String(v) : null"
              />
            </div>

            <!-- Billing type -->
            <div class="grid gap-3">
              <div class="grid gap-1.5">
                <Label class="text-xs text-muted-foreground">Billing type</Label>
                <div class="grid grid-cols-2 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
                  <button
                    type="button"
                    class="rounded py-1.5 text-xs font-medium transition"
                    :class="form.billing_type === 'one_time'
                      ? 'bg-background text-foreground shadow-sm'
                      : 'text-muted-foreground hover:text-foreground'"
                    @click="form.billing_type = 'one_time'"
                  >
                    One-time
                  </button>
                  <button
                    type="button"
                    class="rounded py-1.5 text-xs font-medium transition"
                    :class="form.billing_type === 'recurring'
                      ? 'bg-background text-foreground shadow-sm'
                      : 'text-muted-foreground hover:text-foreground'"
                    @click="form.billing_type = 'recurring'"
                  >
                    Recurring
                  </button>
                </div>
              </div>

              <div class="grid gap-1.5">
                <Label class="text-xs text-muted-foreground">Billing frequency</Label>
                <Select
                  v-model="form.billing_frequency"
                  :disabled="form.billing_type === 'one_time'"
                >
                  <SelectTrigger class="h-9">
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
                <p class="text-[11px] text-muted-foreground">
                  Recurring items require a frequency. One-time items are
                  always billed once.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- ══════ SECTION 3 — PRICING ══════ -->
        <div class="px-6 py-4 border-b border-border">
          <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                    tracking-wider text-muted-foreground">
            <CoinsIcon class="h-3 w-3" />
            Pricing
          </p>

          <div class="grid grid-cols-3 gap-3">
            <!-- Unit -->
            <div class="grid gap-1.5">
              <Label class="text-xs text-muted-foreground">Unit</Label>
              <Select v-model="form.unit">
                <SelectTrigger class="h-9">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="unit in units"
                    :key="unit.id"
                    :value="unit.abbreviation"
                  >
                    {{ unit.abbreviation }}
                    <span v-if="unit.name" class="ml-1 text-muted-foreground">· {{ unit.name }}</span>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Quantity -->
            <div class="grid gap-1.5">
              <Label class="text-xs text-muted-foreground">Quantity</Label>
              <Input
                v-model.number="form.quantity"
                type="number"
                min="0"
                step="0.5"
                class="h-9"
              />
            </div>

            <!-- Unit price -->
            <div class="grid gap-1.5">
              <Label class="text-xs text-muted-foreground">Unit price</Label>
              <div class="relative">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2
                             text-xs font-medium text-muted-foreground">
                  {{ currency }}
                </span>
                <Input
                  v-model.number="form.unit_price"
                  type="number"
                  min="0"
                  class="h-9 pl-11"
                />
              </div>
            </div>
          </div>

          <!-- Line subtotal pill -->
          <div class="mt-3 flex items-center justify-between rounded-md bg-muted/50 px-3 py-2">
            <span class="text-xs text-muted-foreground">
              {{ form.quantity }} × {{ fmt(form.unit_price) }}
            </span>
            <span class="text-sm font-semibold text-foreground">{{ fmt(lineSubtotal) }}</span>
          </div>
        </div>

        <!-- ══════ SECTION 4 — DISCOUNT ══════ -->
        <div class="px-6 py-4 border-b border-border">
          <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                    tracking-wider text-muted-foreground">
            <TagIcon class="h-3 w-3" />
            Item discount <span class="ml-1 font-normal normal-case opacity-60">(optional)</span>
          </p>

          <!-- Type selector -->
          <div class="mb-3 grid grid-cols-3 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
            <button
              v-for="opt in [{ value: 'none', label: 'None' }, { value: 'percentage', label: 'Percent %' }, { value: 'fixed', label: `Fixed ${currency}` }]"
              :key="opt.value"
              type="button"
              class="rounded py-1.5 text-xs font-medium transition"
              :class="form.item_discount_type === opt.value
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
              @click="form.item_discount_type = opt.value as typeof form.item_discount_type"
            >
              {{ opt.label }}
            </button>
          </div>

          <!-- Value input -->
          <div v-if="form.item_discount_type !== 'none'" class="flex items-center gap-3">
            <div class="relative flex-1">
              <Input
                v-model.number="form.item_discount_value"
                type="number"
                min="0"
                :max="form.item_discount_type === 'percentage' ? 100 : undefined"
                class="h-9 pr-10"
                placeholder="0"
              />
              <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2
                           text-xs text-muted-foreground">
                {{ form.item_discount_type === 'percentage' ? '%' : currency }}
              </span>
            </div>
            <div class="text-right">
              <p class="text-[11px] text-muted-foreground">Discount</p>
              <p class="text-sm font-semibold text-red-500 dark:text-red-400">−{{ fmt(discountAmount) }}</p>
            </div>
          </div>
        </div>

        <!-- ══════ SECTION 5 — TAX ══════ -->
        <div class="px-6 py-4 border-b border-border">
          <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                    tracking-wider text-muted-foreground">
            Item tax <span class="ml-1 font-normal normal-case opacity-60">(optional)</span>
          </p>

          <div class="mb-3 grid grid-cols-3 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
            <button
              v-for="opt in [{ value: 'none', label: 'None' }, { value: 'percentage', label: 'Percent %' }, { value: 'fixed', label: `Fixed ${currency}` }]"
              :key="opt.value"
              type="button"
              class="rounded py-1.5 text-xs font-medium transition"
              :class="form.item_tax_type === opt.value
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
              @click="form.item_tax_type = opt.value as typeof form.item_tax_type"
            >
              {{ opt.label }}
            </button>
          </div>

          <div v-if="form.item_tax_type !== 'none'" class="flex items-center gap-3">
            <div class="relative flex-1">
              <Input
                v-model.number="form.item_tax_value"
                type="number"
                min="0"
                class="h-9 pr-10"
                placeholder="0"
              />
              <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2
                           text-xs text-muted-foreground">
                {{ form.item_tax_type === 'percentage' ? '%' : currency }}
              </span>
            </div>
            <div class="text-right">
              <p class="text-[11px] text-muted-foreground">Tax</p>
              <p class="text-sm font-semibold text-foreground">+{{ fmt(taxAmount) }}</p>
            </div>
          </div>
        </div>

        <!-- ══════ SECTION 6 — FLAGS ══════ -->
        <div class="px-6 py-4 border-b border-border">
          <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase
                    tracking-wider text-muted-foreground">
            <ToggleLeftIcon class="h-3 w-3" />
            Options
          </p>

          <div class="space-y-0 divide-y divide-border rounded-lg border border-border overflow-hidden">
            <label class="flex cursor-pointer items-center justify-between px-3 py-2.5
                          hover:bg-muted/50 transition-colors">
              <div>
                <p class="text-xs font-medium text-foreground">Optional item</p>
                <p class="text-[11px] text-muted-foreground">Client can choose to include or exclude this</p>
              </div>
              <Switch v-model:checked="form.is_optional" />
            </label>
          </div>
        </div>

        <!-- ══════ SECTION 7 — TOTALS SUMMARY ══════ -->
        <div class="px-6 py-4">
          <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
            Line summary
          </p>
          <div class="rounded-lg border border-border overflow-hidden">
            <div class="divide-y divide-border text-sm">
              <div class="flex justify-between px-4 py-2.5 text-muted-foreground">
                <span>Subtotal</span>
                <span>{{ fmt(lineSubtotal) }}</span>
              </div>
              <div v-if="form.item_discount_type !== 'none'" class="flex justify-between px-4 py-2.5 text-muted-foreground">
                <span>Discount</span>
                <span class="text-red-500 dark:text-red-400">−{{ fmt(discountAmount) }}</span>
              </div>
              <div v-if="form.item_tax_type !== 'none'" class="flex justify-between px-4 py-2.5 text-muted-foreground">
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

      <!-- Footer -->
      <DialogFooter class="border-t border-border px-6 py-4">
        <Button variant="outline" @click="emit('cancel')">Cancel</Button>
        <Button
          :disabled="!form.description.trim()"
          @click="handleSave"
        >
          {{ item.description ? 'Save changes' : 'Add item' }}
        </Button>
      </DialogFooter>

    </DialogContent>
  </Dialog>
</template>