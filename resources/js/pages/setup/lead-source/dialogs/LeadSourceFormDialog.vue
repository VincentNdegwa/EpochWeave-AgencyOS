<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LeadSourceController from '@/actions/App/Http/Controllers/LeadSourceController';
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
import type { LeadSource } from '@/types/models/lead_source';

interface Props {
    open: boolean;
    source?: LeadSource | null;
}

const props = withDefaults(defineProps<Props>(), {
    source: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    name: props.source?.name || '',
    color: props.source?.color || '#94a3b8',
    category: props.source?.category || '',
    description: props.source?.description || '',
});

watch(
    () => props.source,
    (source) => {
        if (source) {
            form.value = {
                name: source.name,
                color: source.color || '#94a3b8',
                category: source.category || '',
                description: source.description || '',
            };
        } else {
            form.value = {
                name: '',
                color: '#94a3b8',
                category: '',
                description: '',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.source) {
            form.value = {
                name: '',
                color: '#94a3b8',
                category: '',
                description: '',
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
                    {{ source ? 'Edit Lead Source' : 'New Lead Source' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        source
                            ? 'Update the lead source details.'
                            : 'Create a new lead source for tracking account origin.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (source
                        ? LeadSourceController.update.form({
                              lead_source: source.id,
                          })
                        : LeadSourceController.store.form()) as any
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
                        <Label for="lead-source-name" required>Name</Label>
                        <Input
                            id="lead-source-name"
                            name="name"
                            v-model="form.name"
                            placeholder="e.g. Google Ads"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="lead-source-color">Color</Label>
                        <div class="flex items-center gap-2">
                            <input
                                type="color"
                                name="color"
                                v-model="form.color"
                                class="h-9 w-9 cursor-pointer rounded border"
                            />
                            <Input
                                name="color"
                                v-model="form.color"
                                class="flex-1"
                            />
                        </div>
                        <InputError :message="errors.color" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="lead-source-category">Category</Label>
                        <Input
                            id="lead-source-category"
                            name="category"
                            v-model="form.category"
                            placeholder="e.g. Digital, Referral"
                        />
                        <InputError :message="errors.category" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="lead-source-description">Description</Label>
                        <textarea
                            id="lead-source-description"
                            name="description"
                            v-model="form.description"
                            rows="2"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors"
                        ></textarea>
                        <InputError :message="errors.description" />
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
                        {{ source ? 'Update' : 'Create' }} Lead Source
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
