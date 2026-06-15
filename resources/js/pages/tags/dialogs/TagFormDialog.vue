<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TagController from '@/actions/App/Http/Controllers/TagController';
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
import type { Tag } from '@/types/models/tag';

interface Props {
    open: boolean;
    tag?: Tag | null;
}

const props = withDefaults(defineProps<Props>(), {
    tag: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    name: props.tag?.name || '',
    color: props.tag?.color || '#94a3b8',
});

watch(
    () => props.tag,
    (t) => {
        if (t) {
            form.value = {
                name: t.name,
                color: t.color || '#94a3b8',
            };
        } else {
            form.value = {
                name: '',
                color: '#94a3b8',
            };
        }
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && !props.tag) {
            form.value = {
                name: '',
                color: '#94a3b8',
            };
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ tag ? 'Edit Tag' : 'New Tag' }}</DialogTitle>
                <DialogDescription>
                    {{ tag ? 'Update tag details.' : 'Create a new tag for your tasks.' }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (tag
                        ? TagController.update.form({ tag: tag.id })
                        : TagController.store.form()) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                @success="emit('success'); emit('update:open', false);"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="tag-name" required>Name</Label>
                            <Input
                                id="tag-name"
                                name="name"
                                v-model="form.name"
                                required
                                placeholder="e.g. Urgent"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="tag-color">Color</Label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    name="color"
                                    v-model="form.color"
                                    class="h-9 w-9 cursor-pointer rounded border"
                                />
                                <Input name="color" v-model="form.color" class="flex-1" />
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ tag ? 'Update' : 'Create' }} Tag
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
