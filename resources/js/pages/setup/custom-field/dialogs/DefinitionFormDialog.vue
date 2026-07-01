<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import CustomFieldController from '@/actions/App/Http/Controllers/CustomFieldController';
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
import type { CustomFieldDefinition } from '@/types/models/custom_field';

interface Props {
    open: boolean;
    definition?: CustomFieldDefinition | null;
    groupId: number;
}

const props = withDefaults(defineProps<Props>(), {
    definition: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    label: props.definition?.label || '',
    field_type: props.definition?.field_type || 'text',
    options: props.definition?.options?.join('\n') ?? '',
    is_required: props.definition?.is_required ?? false,
    custom_field_group_id: props.groupId,
    sort_order: props.definition?.sort_order.toString() || '0',
});

watch(
    () => props.definition,
    (definition) => {
        if (definition) {
            form.value = {
                label: definition.label,
                field_type: definition.field_type,
                options: definition.options?.join('\n') ?? '',
                is_required: definition.is_required,
                custom_field_group_id: definition.custom_field_group_id,
                sort_order: definition.sort_order.toString(),
            };
        } else {
            form.value = {
                label: '',
                field_type: 'text',
                options: '',
                is_required: false,
                custom_field_group_id: props.groupId,
                sort_order: '0',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.definition) {
            form.value = {
                label: '',
                field_type: 'text',
                options: '',
                is_required: false,
                custom_field_group_id: props.groupId,
                sort_order: '0',
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
                    {{ definition ? 'Edit Field' : 'Add Field' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        definition
                            ? 'Update the field definition.'
                            : 'Create a new custom field definition.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (definition
                        ? CustomFieldController.updateDefinition.form({
                              definition: definition.id,
                          })
                        : CustomFieldController.storeDefinition.form()) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                @success="
                    emit('success');
                    emit('update:open', false);
                "
                v-slot="{ errors, processing }"
            >
                <input
                    type="hidden"
                    name="custom_field_group_id"
                    :value="form.custom_field_group_id"
                />

                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="definition-label" required>Label</Label>
                        <Input
                            id="definition-label"
                            name="label"
                            v-model="form.label"
                            placeholder="e.g. VAT Number"
                            required
                        />
                        <InputError :message="errors.label" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="definition-field-type" required>Field Type</Label>
                        <Select v-model="form.field_type" name="field_type">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select field type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="text">Text</SelectItem>
                                <SelectItem value="number">Number</SelectItem>
                                <SelectItem value="boolean">Boolean</SelectItem>
                                <SelectItem value="date">Date</SelectItem>
                                <SelectItem value="select">Select</SelectItem>
                                <SelectItem value="multiselect">Multiselect</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.field_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="definition-options">Options</Label>
                        <textarea
                            id="definition-options"
                            v-model="form.options"
                            rows="3"
                            placeholder="One option per line"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors"
                        ></textarea>
                        <input
                            v-for="(option, index) in form.options
                                .split('\n')
                                .map((o) => o.trim())
                                .filter(Boolean)"
                            :key="index"
                            type="hidden"
                            name="options[]"
                            :value="option"
                        />
                        <InputError :message="errors.options" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_required" value="0" />
                        <input
                            id="definition-is-required"
                            name="is_required"
                            type="checkbox"
                            value="1"
                            :checked="form.is_required"
                            class="h-4 w-4 rounded border-input"
                        />
                        <Label for="definition-is-required" class="font-normal">Required</Label>
                    </div>

                    <input type="hidden" name="sort_order" :value="form.sort_order" />
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
                        {{ definition ? 'Update' : 'Create' }} Field
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
