<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
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
}>();

const form = ref({
    unit_id: props.product?.unit_id || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    sku: props.product?.sku || '',
    unit_price: props.product?.unit_price || '',
    billing_type: props.product?.billing_type || 'one_time',
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
                is_active: true,
            };
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-lg">
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
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="unit_id" required>Unit</Label>
                        <Select name="unit_id" v-model="form.unit_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Select a unit" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="unit in units"
                                    :key="unit.id"
                                    :value="unit.id.toString()"
                                >
                                    {{ unit.name }} ({{ unit.abbreviation }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.unit_id" />
                    </div>

                    <div class="grid gap-2">
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
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            name="description"
                            v-model="form.description"
                            placeholder="Describe your product or service..."
                            rows="3"
                        />
                        <InputError :message="errors.description" />
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

                    <div class="grid gap-2">
                        <Label for="unit_price" required>Price</Label>
                        <Input
                            id="unit_price"
                            name="unit_price"
                            type="number"
                            v-model="form.unit_price"
                            required
                            placeholder="5000"
                            min="0"
                        />
                        <InputError :message="errors.unit_price" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="billing_type" required>Billing Type</Label>
                        <Select
                            name="billing_type"
                            v-model="form.billing_type"
                            required
                        >
                            <SelectTrigger>
                                <SelectValue
                                    placeholder="Select billing type"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="one_time"
                                    >One Time</SelectItem
                                >
                                <SelectItem value="recurring"
                                    >Recurring</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.billing_type" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Switch
                            id="is_active"
                            name="is_active"
                            v-model="form.is_active"
                        />
                        <Label for="is_active" class="cursor-pointer"
                            >Active</Label
                        >
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
                    <Button type="submit" :disabled="processing">
                        {{ product ? 'Update' : 'Create' }} Product
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
