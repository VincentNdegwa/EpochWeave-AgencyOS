<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Hash,
} from '@lucide/vue';
import ActivityTimeline from '@/components/ActivityTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { useCurrency } from '@/composables/useCurrency';
import { useBillingFrequencies, useBillingTypes } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as productIndex } from '@/routes/products';
import type { Product } from '@/types/models/product';
import ProductActions from './components/ProductActions.vue';

const props = defineProps<{
    product: Product;
    activities: { id: number; type: string; description: string; created_at: string; user?: { id: number; name: string } | null }[];
}>();

const { getVariant, getLabel } = useBillingTypes();
const { getLabel: getFrequencyLabel } = useBillingFrequencies();
const { format: formatCurrency } = useCurrency();

defineOptions({
    layout: {
        title: 'Product Details',
        description: 'View product information',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Products',
                href: productIndex(),
            },
            {
                title: 'Product Details',
            },
        ],
    },
});
</script>

<template>
    <Head title="Product Details" />

    <div class="flex flex-col">
        <!-- Header -->
        <div class="sticky top-0 z-30 border-b pb-4 border-border bg-background/95 backdrop-blur-sm print:hidden">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge
                            :variant="props.product.is_active ? 'default' : 'secondary'"
                            class="rounded-md text-[11px]"
                        >
                            {{ props.product.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                        <span class="inline-flex items-center gap-1 rounded-md border border-border bg-muted/50 px-2 py-0.5 font-mono text-[11px] text-muted-foreground">
                            <Hash class="h-2.5 w-2.5" />{{ props.product.sku ?? props.product.id }}
                        </span>
                    </div>
                    <h1 class="text-base font-semibold text-foreground">
                        {{ props.product.name }}
                        <span class="font-normal text-muted-foreground">· {{ formatCurrency(props.product.unit_price) }}</span>
                    </h1>
                </div>
                <ProductActions
                    :product="props.product"
                    variant="split"
                    size="sm"
                />
            </div>
        </div>

        <!-- Stats -->
        <div class="border-b border-border bg-background print:hidden">
            <div class="grid grid-cols-2 divide-x divide-border sm:grid-cols-5">
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Price</p>
                    <p class="mt-0.5 text-xl font-bold tabular-nums text-foreground">{{ formatCurrency(props.product.unit_price) }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Billing Type</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ getLabel(props.product.billing_type) }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Frequency</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ getFrequencyLabel(props.product.billing_frequency) }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Status</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ props.product.is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">Unit</p>
                    <p class="mt-0.5 text-base font-bold text-foreground">{{ props.product.unit?.name || '—' }}</p>
                    <p v-if="props.product.unit?.abbreviation" class="text-[10px] text-muted-foreground">{{ props.product.unit.abbreviation }}</p>
                </div>
            </div>
        </div>

        <div class="space-y-4 pt-4">
            <div class="border-b p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Product Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">SKU</span>
                        <span class="font-medium font-mono">{{ props.product.sku || '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Billing Type</span>
                        <span class="font-medium">{{ getLabel(props.product.billing_type) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Frequency</span>
                        <span class="font-medium">{{ getFrequencyLabel(props.product.billing_frequency) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Unit</span>
                        <span class="font-medium">{{ props.product.unit?.name || '—' }}</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-medium text-muted-foreground">Description</p>
                    <p class="text-sm leading-relaxed text-foreground/90">
                        {{ props.product.description || 'No description provided.' }}
                    </p>
                </div>
            </div>

            <div class="border-b p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Activity</h3>
                <ActivityTimeline :activities="props.activities" />
            </div>
        </div>
    </div>
</template>
