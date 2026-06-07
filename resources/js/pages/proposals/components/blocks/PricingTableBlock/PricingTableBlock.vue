<script setup lang="ts">
import { computed } from 'vue';
import { PlusIcon, Trash2Icon, GripVerticalIcon } from '@lucide/vue';
import { nanoid } from 'nanoid';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import type { BaseBlock, PricingTableBlockData, PricingLineItem } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  data: PricingTableBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<PricingTableBlockData>) => {
  store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const updateItem = (id: string, changes: Partial<PricingLineItem>) => {
  updateData({
    items: props.data.items.map((item) =>
      item.id === id
        ? { ...item, ...changes, subtotal: changes.quantity !== undefined || changes.unit_price !== undefined
            ? (changes.quantity ?? item.quantity) * (changes.unit_price ?? item.unit_price)
            : item.subtotal }
        : item,
    ),
  });
};

const addItem = () => {
  updateData({
    items: [
      ...props.data.items,
      {
        id: nanoid(8),
        description: 'New line item',
        unit: 'hr',
        quantity: 1,
        unit_price: 0,
        subtotal: 0,
        is_optional: false,
        billing_type: 'one_time',
        product_id: null,
      },
    ],
  });
};

const removeItem = (id: string) => {
  updateData({ items: props.data.items.filter((i) => i.id !== id) });
};

const fmt = (cents: number) =>
  new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.data.currency || 'USD',
    minimumFractionDigits: 0,
  }).format(cents);

const subtotal = computed(() =>
  props.data.items.filter((i) => !i.is_optional).reduce((s, i) => s + i.subtotal, 0),
);

const discountAmount = computed(() => {
  if (!props.data.discount) return 0;
  if (props.data.discount.type === 'percentage')
    return Math.round(subtotal.value * props.data.discount.value / 100);
  return props.data.discount.amount;
});

const taxBase = computed(() => subtotal.value - discountAmount.value);
const taxAmount = computed(() =>
  props.data.show_tax_row ? Math.round(taxBase.value * props.data.tax_rate / 100) : 0,
);
const grandTotal = computed(() => taxBase.value + taxAmount.value);
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

    <div class="w-full overflow-hidden rounded-lg border border-border">
      <!-- Table header -->
      <div
        class="grid border-b border-border bg-muted/50 px-4 py-2.5 text-[11px]
               font-semibold uppercase tracking-wider text-muted-foreground"
        :class="[
          data.show_quantity_column && data.show_unit_column ? 'grid-cols-[1fr_60px_60px_100px]'
          : data.show_quantity_column || data.show_unit_column ? 'grid-cols-[1fr_60px_100px]'
          : 'grid-cols-[1fr_100px]'
        ]"
      >
        <span>Description</span>
        <span v-if="data.show_unit_column" class="text-center">Unit</span>
        <span v-if="data.show_quantity_column" class="text-center">Qty</span>
        <span class="text-right">
          {{ data.show_subtotal_per_line ? 'Subtotal' : 'Price' }}
        </span>
      </div>

      <!-- Line items -->
      <div class="divide-y divide-border">
        <div
          v-for="item in data.items"
          :key="item.id"
          class="group/row grid items-center gap-2 px-4 py-3 transition hover:bg-muted/20"
          :class="[
            data.show_quantity_column && data.show_unit_column ? 'grid-cols-[1fr_60px_60px_100px]'
            : data.show_quantity_column || data.show_unit_column ? 'grid-cols-[1fr_60px_100px]'
            : 'grid-cols-[1fr_100px]',
            item.is_optional ? 'opacity-60' : '',
          ]"
        >
          <!-- Description -->
          <div class="flex items-center gap-2 min-w-0">
            <GripVerticalIcon
              v-if="!isLocked"
              class="h-3.5 w-3.5 flex-shrink-0 cursor-grab text-muted-foreground/40
                     opacity-0 group-hover/row:opacity-100"
            />
            <div class="min-w-0">
              <input
                v-if="!isLocked"
                type="text"
                :value="item.description"
                class="w-full border-none bg-transparent text-sm text-foreground
                       outline-none placeholder:text-muted-foreground"
                placeholder="Line item description"
                @input="updateItem(item.id, { description: ($event.target as HTMLInputElement).value })"
              />
              <span v-else class="text-sm text-foreground">{{ item.description }}</span>
              <span
                v-if="item.is_optional"
                class="ml-1 text-[10px] text-muted-foreground"
              >Optional</span>
              <span
                v-if="item.billing_type === 'recurring'"
                class="ml-1 rounded bg-muted px-1 py-0.5 text-[10px] text-muted-foreground"
              >Recurring</span>
            </div>
          </div>

          <!-- Unit -->
          <input
            v-if="data.show_unit_column && !isLocked"
            type="text"
            :value="item.unit"
            class="w-full border-none bg-transparent text-center text-sm
                   text-muted-foreground outline-none"
            @input="updateItem(item.id, { unit: ($event.target as HTMLInputElement).value })"
          />
          <span v-else-if="data.show_unit_column" class="text-center text-sm text-muted-foreground">
            {{ item.unit }}
          </span>

          <!-- Quantity -->
          <input
            v-if="data.show_quantity_column && !isLocked"
            type="number"
            min="0"
            :value="item.quantity"
            class="w-full border-none bg-transparent text-center text-sm
                   text-foreground outline-none"
            @input="updateItem(item.id, { quantity: Number(($event.target as HTMLInputElement).value) })"
          />
          <span v-else-if="data.show_quantity_column" class="text-center text-sm text-foreground">
            {{ item.quantity }}
          </span>

          <!-- Price / Subtotal -->
          <div class="flex items-center justify-end gap-1">
            <template v-if="!isLocked">
              <span class="text-xs text-muted-foreground">{{ data.currency }}</span>
              <input
                type="number"
                min="0"
                :value="item.unit_price"
                class="w-20 border-none bg-transparent text-right text-sm
                       text-foreground outline-none"
                @input="updateItem(item.id, { unit_price: Number(($event.target as HTMLInputElement).value) })"
              />
            </template>
            <span v-else class="text-sm font-medium text-foreground">
              {{ fmt(data.show_subtotal_per_line ? item.subtotal : item.unit_price) }}
            </span>

            <!-- Delete -->
            <button
              v-if="!isLocked"
              type="button"
              class="ml-1 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded
                     text-muted-foreground opacity-0 transition hover:text-destructive
                     group-hover/row:opacity-100"
              @click="removeItem(item.id)"
            >
              <Trash2Icon class="h-3 w-3" />
            </button>
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-if="data.items.length === 0 && !isLocked"
          class="px-4 py-6 text-center text-sm text-muted-foreground"
        >
          No items yet. Add a line item below.
        </div>
      </div>

      <!-- Add row -->
      <button
        v-if="!isLocked"
        type="button"
        class="flex w-full items-center gap-2 border-t border-border px-4 py-2.5
               text-xs text-muted-foreground transition hover:bg-muted/40 hover:text-foreground"
        @click="addItem"
      >
        <PlusIcon class="h-3.5 w-3.5" />
        Add line item
      </button>
    </div>

    <!-- Totals -->
    <div class="mt-4 flex justify-end">
      <div class="w-64 space-y-1.5 text-sm">
        <div v-if="data.show_subtotal_row" class="flex justify-between text-muted-foreground">
          <span>Subtotal</span>
          <span>{{ fmt(subtotal) }}</span>
        </div>
        <div v-if="data.discount && discountAmount" class="flex justify-between text-muted-foreground">
          <span>{{ data.discount.label || 'Discount' }}</span>
          <span>−{{ fmt(discountAmount) }}</span>
        </div>
        <div v-if="data.show_tax_row" class="flex justify-between text-muted-foreground">
          <span>{{ data.tax_label }} ({{ data.tax_rate }}%)</span>
          <span>{{ fmt(taxAmount) }}</span>
        </div>
        <div v-if="data.show_total_row" class="flex justify-between border-t border-border pt-2 font-semibold text-foreground">
          <span>Total</span>
          <span>{{ fmt(grandTotal) }}</span>
        </div>
        <p v-if="data.footer_note" class="pt-1 text-[11px] text-muted-foreground">
          {{ data.footer_note }}
        </p>
      </div>
    </div>
  </section>
</template>