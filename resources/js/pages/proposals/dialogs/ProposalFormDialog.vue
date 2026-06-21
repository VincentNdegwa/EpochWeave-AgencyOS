<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { ref, watch } from 'vue';
import { onMounted } from 'vue';
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
import { useBuilderDataStore } from '@/stores/builderData';
import type { Account } from '@/types/models/account';
import type { Proposal } from '@/types/models/proposal';

interface Props {
    open: boolean;
    proposal?: Proposal | null;
    account?: Account | null;
}

const props = withDefaults(defineProps<Props>(), {
    proposal: null,
    account: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const builderDataStore = useBuilderDataStore();
const { accounts, templates, accountContacts } = storeToRefs(builderDataStore);

const form = ref({
    title: props.proposal?.title || '',
    description: props.proposal?.description || '',
    account_id:
        props.proposal?.account_id?.toString() ||
        props.account?.id?.toString() ||
        '',
    account_contact_id: props.proposal?.account_contact_id || '',
    template_id: props.proposal?.template_id || '',
});

// Fetch data when dialog opens
onMounted(() => {
    builderDataStore.fetchAccounts();
    builderDataStore.fetchTemplates();
});

// Fetch contacts when account changes
watch(
    () => form.value.account_id,
    async (newAccountId) => {
        if (newAccountId) {
            const contacts = await builderDataStore.fetchAccountContacts({
                account_id: newAccountId.toString(),
            });

            // Auto-select first contact if available
            if (contacts && contacts.length > 0) {
                form.value.account_contact_id = contacts[0].id.toString();
            } else {
                form.value.account_contact_id = '';
            }
        } else {
            builderDataStore.accountContacts = [];
            form.value.account_contact_id = '';
        }
    },
    { immediate: true },
);

watch(
    () => props.proposal,
    (newProposal) => {
        if (newProposal) {
            form.value = {
                title: newProposal.title,
                description: newProposal.description || '',
                account_id: newProposal.account_id?.toString() || '',
                account_contact_id:
                    newProposal.account_contact_id?.toString() || '',
                template_id: newProposal.template_id?.toString() || '',
            };
        } else {
            form.value = {
                title: '',
                description: '',
                account_id: props.account?.id?.toString() || '',
                account_contact_id: '',
                template_id: '',
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
                        ? ProposalController.update.form({
                              proposal: proposal.id,
                          })
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
                                placeholder="Enter proposal title..."
                            />
                            <InputError :message="errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="account_id" required>Account</Label>
                            <Select
                                name="account_id"
                                v-model="form.account_id"
                                required
                                :disabled="!!props.account"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Select an account"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="account in accounts"
                                        :key="account.id"
                                        :value="account.id.toString()"
                                    >
                                        {{
                                            account.company_name || account.name
                                        }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.account_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="account_contact_id">Contact</Label>
                            <Select
                                name="account_contact_id"
                                v-model="form.account_contact_id"
                                :disabled="!form.account_id"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Select a contact"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="contact in accountContacts"
                                        :key="contact.id"
                                        :value="contact.id.toString()"
                                    >
                                        {{ contact.first_name }}
                                        {{ contact.last_name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.account_contact_id" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="template_id">Template</Label>
                            <Select
                                name="template_id"
                                v-model="form.template_id"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Select a template (optional)"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="template in templates"
                                        :key="template.id"
                                        :value="template.id.toString()"
                                    >
                                        {{ template.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.template_id" />
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
