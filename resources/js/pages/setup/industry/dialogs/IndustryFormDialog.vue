<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import IndustryController from '@/actions/App/Http/Controllers/IndustryController';
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
import type { Industry } from '@/types/models/industry';

interface Props {
    open: boolean;
    industry?: Industry | null;
}

const props = withDefaults(defineProps<Props>(), {
    industry: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    name: props.industry?.name || '',
    color: props.industry?.color || '#94a3b8',
    description: props.industry?.description || '',
});

watch(
    () => props.industry,
    (industry) => {
        if (industry) {
            form.value = {
                name: industry.name,
                color: industry.color || '#94a3b8',
                description: industry.description || '',
            };
        } else {
            form.value = {
                name: '',
                color: '#94a3b8',
                description: '',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.industry) {
            form.value = {
                name: '',
                color: '#94a3b8',
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
                    {{ industry ? 'Edit Industry' : 'New Industry' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        industry
                            ? 'Update the industry details.'
                            : 'Create a new industry for your accounts.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (industry
                        ? IndustryController.update.form({
                              industry: industry.id,
                          })
                        : IndustryController.store.form()) as any
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
                        <Label for="industry-name" required>Name</Label>
                        <Input
                            id="industry-name"
                            name="name"
                            v-model="form.name"
                            placeholder="e.g. Technology"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="industry-color">Color</Label>
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
                        <Label for="industry-description">Description</Label>
                        <textarea
                            id="industry-description"
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
                        {{ industry ? 'Update' : 'Create' }} Industry
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
