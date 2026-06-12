<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref, watch } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useBillingFrequencies } from '@/composables/useEnums';
import type { Product, ProductUnit } from '@/types/models/product';

interface Props {
    open: boolean;
    product?: Product | null;
    units?: ProductUnit[];
}

const props = withDefaults(defineProps<Props>(), {
    product: null,
    units: () => [],
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
    'create-unit': [];
}>();

const { values: billingFrequencyOptions } = useBillingFrequencies();

const form = ref({
    unit_id: props.product?.unit_id || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    sku: props.product?.sku || '',
    unit_price: props.product?.unit_price || '',
    billing_type: props.product?.billing_type || 'one_time',
    billing_frequency:
        props.product?.billing_frequency ||
        (props.product?.billing_type === 'recurring' ? 'monthly' : 'none'),
    is_active: props.product?.is_active ?? true,
});

watch(
    () => props.product,
    (newProduct) => {
        if (newProduct) {
            form.value = {
                unit_id: newProduct.unit_id,
                name: newProduct.name,
                description: newProduct.description || '',
                sku: newProduct.sku || '',
                unit_price: newProduct.unit_price,
                billing_type: newProduct.billing_type,
                billing_frequency:
                    newProduct.billing_frequency ||
                    (newProduct.billing_type === 'recurring'
                        ? 'monthly'
                        : 'none'),
                is_active: newProduct.is_active,
            };
        } else {
            form.value = {
                unit_id: '',
                name: '',
                description: '',
                sku: '',
                unit_price: '',
                billing_type: 'one_time',
                billing_frequency: 'none',
                is_active: true,
            };
        }
    },
);

watch(
    () => form.value.billing_type,
    (type) => {
        if (type === 'one_time') {
            form.value.billing_frequency = 'none';
        } else if (form.value.billing_frequency === 'none') {
            form.value.billing_frequency = 'monthly';
        }
    },
);
</script>

<template>
    <Dialog class="" :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl md:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{
                    product ? 'Edit Product' : 'New Product'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        product
                            ? 'Update the product information below.'
                            : 'Add a new product or service to your catalog.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (product
                        ? ProductController.update.form({ product: product.id })
                        : ProductController.store.form()) as any
                "
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="name" required>Name</Label>
                            <Input
                                id="name"
                                name="name"
                                v-model="form.name"
                                required
                                placeholder="Web Development"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <Label for="unit_id" required>Unit</Label>
                                <Button
                                    v-if="!units.length"
                                    type="button"
                                    variant="outline"
                                    size="xs"
                                    @click="emit('create-unit')"
                                >
                                    <Plus class="mr-1 h-3.5 w-3.5" />
                                    Create unit
                                </Button>
                            </div>
                            <Select
                                name="unit_id"
                                v-model="form.unit_id"
                                required
                                :disabled="units.length === 0"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        :placeholder="
                                            units.length
                                                ? 'Select a unit'
                                                : 'Create a unit first'
                                        "
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="unit in units"
                                        :key="unit.id"
                                        :value="unit.id.toString()"
                                    >
                                        {{ unit.name }} ({{
                                            unit.abbreviation
                                        }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.unit_id" />
                            <div
                                v-if="!units.length"
                                class="text-xs text-muted-foreground"
                            >
                                You need at least one unit before creating a
                                product.
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="sku">SKU</Label>
                            <Input
                                id="sku"
                                name="sku"
                                v-model="form.sku"
                                placeholder="WEB-001"
                            />
                            <InputError :message="errors.sku" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                name="description"
                                v-model="form.description"
                                placeholder="Describe your product or service..."
                                rows="4"
                            />
                            <InputError :message="errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="unit_price" required>Price</Label>
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"
                                >
                                    $
                                </div>
                                <Input
                                    id="unit_price"
                                    name="unit_price"
                                    type="number"
                                    v-model="form.unit_price"
                                    required
                                    placeholder="5000"
                                    min="0"
                                    class="pl-7"
                                />
                            </div>
                            <InputError :message="errors.unit_price" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="billing_type" required>
                                Billing Type
                            </Label>
                            <Select
                                name="billing_type"
                                v-model="form.billing_type"
                                required
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Select billing type"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="one_time">
                                        One Time
                                    </SelectItem>
                                    <SelectItem value="recurring">
                                        Recurring
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.billing_type" />
                        </div>

                        <input
                            type="hidden"
                            name="billing_frequency"
                            :value="form.billing_frequency"
                        />

                        <div class="grid gap-2">
                            <Label for="billing_frequency" required>
                                Billing Frequency
                            </Label>

                            <template v-if="form.billing_type === 'recurring'">
                                <Select
                                    name="billing_frequency"
                                    v-model="form.billing_frequency"
                                    required
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Select billing frequency"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in billingFrequencyOptions"
                                            :key="option.value"
                                            :value="option.value"
                                            :disabled="option.value === 'none'"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.billing_frequency"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Recurring items must specify how often they
                                    renew.
                                </p>
                            </template>

                            <template v-else>
                                <div
                                    class="rounded-md border border-dashed border-border px-3 py-2 text-xs text-muted-foreground"
                                >
                                    Billing frequency set to
                                    <strong>None</strong>
                                    for one-time charges.
                                </div>
                            </template>
                        </div>

                        <div
                            class="flex items-center justify-between gap-4 rounded-lg border border-border bg-muted/30 px-3 py-2 sm:col-span-2"
                        >
                            <div class="grid gap-0.5">
                                <Label for="is_active" class="cursor-pointer">
                                    Active
                                </Label>
                                <div class="text-xs text-muted-foreground">
                                    Disable to hide this product from new
                                    proposals.
                                </div>
                            </div>
                            <input
                                type="hidden"
                                name="is_active"
                                :value="form.is_active ? '1' : '0'"
                            />
                            <Switch id="is_active" v-model="form.is_active" />
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="processing || !units.length"
                    >
                        {{ product ? 'Update' : 'Create' }} Product
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
