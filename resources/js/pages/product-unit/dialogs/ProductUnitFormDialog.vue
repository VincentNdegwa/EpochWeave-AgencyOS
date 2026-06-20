<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ProductUnitController from '@/actions/App/Http/Controllers/ProductUnitController';
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
import type { ProductUnit } from '@/types/models/product';

interface Props {
    open: boolean;
    unit?: ProductUnit | null;
}

const props = withDefaults(defineProps<Props>(), {
    unit: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    name: props.unit?.name || '',
    abbreviation: props.unit?.abbreviation || '',
});

watch(
    () => props.unit,
    (newUnit) => {
        if (newUnit) {
            form.value = {
                name: newUnit.name,
                abbreviation: newUnit.abbreviation,
            };
        } else {
            form.value = {
                name: '',
                abbreviation: '',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.unit) {
            form.value = {
                name: '',
                abbreviation: '',
            };
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{
                    unit ? 'Edit Product Unit' : 'New Product Unit'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        unit
                            ? 'Update the product unit information below.'
                            : 'Add a new measurement unit for your products.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (unit
                        ? ProductUnitController.update.form({
                              product_unit: unit.id,
                          })
                        : ProductUnitController.store.form()) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                @success="
                    emit('success');
                    emit('update:open', false);
                "
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="name" required>Name</Label>
                        <Input
                            id="name"
                            name="name"
                            v-model="form.name"
                            required
                            placeholder="Enter unit name..."
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="abbreviation" required>Abbreviation</Label>
                        <Input
                            id="abbreviation"
                            name="abbreviation"
                            v-model="form.abbreviation"
                            required
                            placeholder="Enter abbreviation..."
                            maxlength="10"
                        />
                        <InputError :message="errors.abbreviation" />
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
                        {{ unit ? 'Update' : 'Create' }} Unit
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
