<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import CompanySizeController from '@/actions/App/Http/Controllers/CompanySizeController';
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
import type { CompanySize } from '@/types/models/company_size';

interface Props {
    open: boolean;
    size?: CompanySize | null;
}

const props = withDefaults(defineProps<Props>(), {
    size: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    label: props.size?.label || '',
    min_employees: props.size?.min_employees?.toString() ?? '',
    max_employees: props.size?.max_employees?.toString() ?? '',
});

watch(
    () => props.size,
    (size) => {
        if (size) {
            form.value = {
                label: size.label,
                min_employees: size.min_employees?.toString() ?? '',
                max_employees: size.max_employees?.toString() ?? '',
            };
        } else {
            form.value = {
                label: '',
                min_employees: '',
                max_employees: '',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.size) {
            form.value = {
                label: '',
                min_employees: '',
                max_employees: '',
            };
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{ size ? 'Edit Company Size' : 'New Company Size' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        size
                            ? 'Update the company size details.'
                            : 'Create a new company size range for your accounts.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (size
                        ? CompanySizeController.update.form({
                              company_size: size.id,
                          })
                        : CompanySizeController.store.form()) as any
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
                        <Label for="company-size-label" required>Label</Label>
                        <Input
                            id="company-size-label"
                            name="label"
                            v-model="form.label"
                            placeholder="e.g. 11-50"
                            required
                        />
                        <InputError :message="errors.label" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="company-size-min">Min Employees</Label>
                        <Input
                            id="company-size-min"
                            name="min_employees"
                            type="number"
                            v-model="form.min_employees"
                        />
                        <InputError :message="errors.min_employees" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="company-size-max">Max Employees</Label>
                        <Input
                            id="company-size-max"
                            name="max_employees"
                            type="number"
                            v-model="form.max_employees"
                        />
                        <InputError :message="errors.max_employees" />
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
                        {{ size ? 'Update' : 'Create' }} Company Size
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
