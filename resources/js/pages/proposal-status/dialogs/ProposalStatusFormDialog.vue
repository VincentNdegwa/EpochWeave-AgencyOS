<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ProposalStatusController from '@/actions/App/Http/Controllers/ProposalStatusController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import ColorPresets from '@/components/ui/color-presets/ColorPresets.vue';
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
import type { ProposalStatusModel } from '@/types/models/proposal';

interface Props {
    open: boolean;
    status?: ProposalStatusModel | null;
}

const props = withDefaults(defineProps<Props>(), {
    status: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    title: props.status?.title || '',
    color: props.status?.color || '#3b82f6',
});

watch(
    () => props.status,
    (newStatus) => {
        if (newStatus) {
            form.value = {
                title: newStatus.title,
                color: newStatus.color,
            };
        } else {
            form.value = {
                title: '',
                color: '#3b82f6',
            };
        }
    },
);
</script>

<template>
    <Dialog class="" :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>{{
                    status ? 'Edit Proposal Status' : 'New Proposal Status'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        status
                            ? 'Update the proposal status information below.'
                            : 'Create a new status for your proposal workflow.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (status
                        ? ProposalStatusController.update.form({
                              proposal_status: status.id,
                          })
                        : ProposalStatusController.store.form()) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                @success="
                    emit('success');
                    emit('update:open', false);
                "
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4">
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="title" required>Status Name</Label>
                            <Input
                                id="title"
                                name="title"
                                v-model="form.title"
                                required
                                placeholder="In Review"
                            />
                            <InputError :message="errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="color" required>Color</Label>
                            <input
                                type="hidden"
                                name="color"
                                :value="form.color"
                            />
                            <ColorPresets
                                v-model="form.color"
                                placeholder="#3b82f6"
                            />
                            <InputError :message="errors.color" />
                            <p class="text-xs text-muted-foreground">
                                Choose a color to represent this status in your
                                proposal workflow.
                            </p>
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
                    <Button type="submit" :disabled="processing">
                        {{ status ? 'Update' : 'Create' }} Status
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
