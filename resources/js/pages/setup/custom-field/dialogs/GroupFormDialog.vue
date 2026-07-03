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
import type { CustomFieldGroup } from '@/types/models/custom_field';

interface Props {
    open: boolean;
    group?: CustomFieldGroup | null;
}

const props = withDefaults(defineProps<Props>(), {
    group: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    name: props.group?.name || '',
    applies_to: props.group?.applies_to || '',
    sort_order: props.group?.sort_order.toString() || '0',
});

watch(
    () => props.group,
    (group) => {
        if (group) {
            form.value = {
                name: group.name,
                applies_to: group.applies_to,
                sort_order: group.sort_order.toString(),
            };
        } else {
            form.value = {
                name: '',
                applies_to: '',
                sort_order: '0',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.group) {
            form.value = {
                name: '',
                applies_to: '',
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
                    {{ group ? 'Edit Group' : 'New Group' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        group
                            ? 'Update the field group details.'
                            : 'Create a new custom field group.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (group
                        ? CustomFieldController.updateGroup.form({
                              group: group.id,
                          })
                        : CustomFieldController.storeGroup.form()) as any
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
                        <Label for="group-name" required>Name</Label>
                        <Input
                            id="group-name"
                            name="name"
                            v-model="form.name"
                            placeholder="e.g. Company Details"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="group-applies-to" required
                            >Applies To</Label
                        >
                        <Input
                            id="group-applies-to"
                            name="applies_to"
                            v-model="form.applies_to"
                            placeholder="e.g. App\Models\Account"
                            required
                        />
                        <InputError :message="errors.applies_to" />
                    </div>

                    <input
                        type="hidden"
                        name="sort_order"
                        :value="form.sort_order"
                    />
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
                        {{ group ? 'Update' : 'Create' }} Group
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
