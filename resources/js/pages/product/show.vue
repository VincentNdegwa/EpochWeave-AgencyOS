<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Edit,
    Trash2,
    Package,
    DollarSign,
    Clock,
    CheckCircle2,
    XCircle,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useCurrency } from '@/composables/useCurrency';
import { useBillingFrequencies, useBillingTypes } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as productIndex } from '@/routes/products';
import type { Product } from '@/types/models/product';

const props = defineProps<{
    product: Product;
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

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ props.product.name }}
                    </h1>
                    <p class="text-muted-foreground">
                        Product details and pricing information
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button variant="outline">
                    <Edit class="mr-2 h-4 w-4" />
                    Edit
                </Button>
                <Button variant="destructive">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </Button>
            </div>
        </div>

        <!-- Product Details -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">Price</CardTitle>
                    <DollarSign class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ formatCurrency(props.product.unit_price) }}
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Billing Type</CardTitle
                    >
                    <Clock class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <Badge :variant="getVariant(props.product.billing_type)">
                        {{ getLabel(props.product.billing_type) }}
                    </Badge>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium"
                        >Billing Frequency</CardTitle
                    >
                    <Clock class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <Badge variant="secondary">
                        {{ getFrequencyLabel(props.product.billing_frequency) }}
                    </Badge>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">Status</CardTitle>
                    <CheckCircle2
                        v-if="props.product.is_active"
                        class="h-4 w-4 text-muted-foreground"
                    />
                    <XCircle v-else class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <Badge
                        :variant="
                            props.product.is_active ? 'default' : 'secondary'
                        "
                    >
                        {{ props.product.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">Unit</CardTitle>
                    <Package class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ props.product.unit?.name || '—' }}
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ props.product.unit?.abbreviation || '' }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Additional Details -->
        <Card>
            <CardHeader>
                <CardTitle>Product Information</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div>
                    <div class="mb-1 text-sm font-medium text-muted-foreground">
                        SKU
                    </div>
                    <div class="font-mono text-sm">
                        {{ props.product.sku || '—' }}
                    </div>
                </div>
                <div>
                    <div class="mb-1 text-sm font-medium text-muted-foreground">
                        Description
                    </div>
                    <div class="text-sm">
                        {{
                            props.product.description ||
                            'No description provided.'
                        }}
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
