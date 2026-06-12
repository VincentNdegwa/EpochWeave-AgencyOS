<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ProposalController from '@/actions/App/Http/Controllers/ProposalController';
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
import { Textarea } from '@/components/ui/textarea';
import type { Proposal } from '@/types/models/proposal';

interface Props {
    open: boolean;
    proposal?: Proposal | null;
}

const props = withDefaults(defineProps<Props>(), {
    proposal: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    title: props.proposal?.title || '',
    description: props.proposal?.description || '',
    account_id: props.proposal?.account_id || '',
    grand_total: props.proposal?.grand_total || '',
    currency: props.proposal?.currency || 'USD',
});

watch(
    () => props.proposal,
    (newProposal) => {
        if (newProposal) {
            form.value = {
                title: newProposal.title,
                description: newProposal.description || '',
                account_id: newProposal.account_id?.toString() || '',
                grand_total: newProposal.grand_total?.toString() || '',
                currency: newProposal.currency || 'USD',
            };
        } else {
            form.value = {
                title: '',
                description: '',
                account_id: '',
                grand_total: '',
                currency: 'USD',
            };
        }
    },
);
</script>

<template>
    <Dialog class="" :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl md:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{
                    proposal ? 'Edit Proposal' : 'New Proposal'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        proposal
                            ? 'Update the proposal information below.'
                            : 'Create a new proposal for your client.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (proposal
                        ? ProposalController.update.form({ proposal: proposal.id })
                        : ProposalController.store.form()) as any
                "
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="title" required>Title</Label>
                            <Input
                                id="title"
                                name="title"
                                v-model="form.title"
                                required
                                placeholder="Website Redesign Proposal"
                            />
                            <InputError :message="errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="account_id" required>Account</Label>
                            <Select
                                name="account_id"
                                v-model="form.account_id"
                                required
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select an account" />
                                </SelectTrigger>
                                <SelectContent>
                                    <!-- Account options would be populated here -->
                                    <SelectItem value="1">Example Account</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.account_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="currency">Currency</Label>
                            <Select
                                name="currency"
                                v-model="form.currency"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select currency" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="USD">USD</SelectItem>
                                    <SelectItem value="EUR">EUR</SelectItem>
                                    <SelectItem value="GBP">GBP</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.currency" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                name="description"
                                v-model="form.description"
                                placeholder="Describe the proposal details..."
                                rows="4"
                            />
                            <InputError :message="errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="grand_total">Total Amount</Label>
                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"
                                >
                                    {{ form.currency }}
                                </div>
                                <Input
                                    id="grand_total"
                                    name="grand_total"
                                    type="number"
                                    v-model="form.grand_total"
                                    placeholder="5000"
                                    min="0"
                                    step="0.01"
                                    class="pl-12"
                                />
                            </div>
                            <InputError :message="errors.grand_total" />
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
                        {{ proposal ? 'Update' : 'Create' }} Proposal
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
