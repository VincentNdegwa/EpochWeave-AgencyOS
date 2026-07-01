<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AccountContactController from '@/actions/App/Http/Controllers/AccountContactController';
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
import { Switch } from '@/components/ui/switch';
import type { AccountContact } from '@/types/models/account';

interface Props {
    open: boolean;
    accountId: number;
    contact?: AccountContact | null;
}

const props = withDefaults(defineProps<Props>(), {
    contact: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const form = ref({
    first_name: props.contact?.first_name || '',
    last_name: props.contact?.last_name || '',
    email: props.contact?.email || '',
    phone: props.contact?.phone || '',
    job_title: props.contact?.job_title || '',
    date_of_birth: props.contact?.date_of_birth || '',
    department: props.contact?.department || '',
    preferred_contact_method: props.contact?.preferred_contact_method || '',
    notes: props.contact?.notes || '',
    is_primary: props.contact?.is_primary || false,
    receives_billing: props.contact?.receives_billing || false,
});

watch(
    () => props.contact,
    (newContact) => {
        if (newContact) {
            form.value = {
                first_name: newContact.first_name,
                last_name: newContact.last_name,
                email: newContact.email,
                phone: newContact.phone || '',
                job_title: newContact.job_title || '',
                date_of_birth: newContact.date_of_birth || '',
                department: newContact.department || '',
                preferred_contact_method: newContact.preferred_contact_method || '',
                notes: newContact.notes || '',
                is_primary: newContact.is_primary,
                receives_billing: newContact.receives_billing,
            };
        } else {
            form.value = {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                job_title: '',
                date_of_birth: '',
                department: '',
                preferred_contact_method: '',
                notes: '',
                is_primary: false,
                receives_billing: false,
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
                    contact ? 'Edit Contact' : 'Add Contact'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        contact
                            ? 'Update the contact information below.'
                            : 'Add a new contact to this account.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="
                    (contact
                        ? AccountContactController.update.form({
                              account: accountId,
                              contact: contact.id,
                          })
                        : AccountContactController.store.form({
                              account: accountId,
                          })) as any
                "
                :options="{ preserveScroll: true, preserveState: true }"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="first_name" required>First Name</Label>
                        <Input
                            id="first_name"
                            name="first_name"
                            v-model="form.first_name"
                            required
                            placeholder="Enter first name..."
                        />
                        <InputError :message="errors.first_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="last_name" required>Last Name</Label>
                        <Input
                            id="last_name"
                            name="last_name"
                            v-model="form.last_name"
                            required
                            placeholder="Enter last name..."
                        />
                        <InputError :message="errors.last_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" required>Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            v-model="form.email"
                            required
                            placeholder="contact@company.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            v-model="form.phone"
                            placeholder="+1 (555) 123-4567"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job_title">Job Title</Label>
                        <Input
                            id="job_title"
                            name="job_title"
                            v-model="form.job_title"
                            placeholder="Enter job title..."
                        />
                        <InputError :message="errors.job_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="department">Department</Label>
                        <Input
                            id="department"
                            name="department"
                            v-model="form.department"
                            placeholder="e.g. Engineering"
                        />
                        <InputError :message="errors.department" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="date_of_birth">Date of Birth</Label>
                        <Input
                            id="date_of_birth"
                            name="date_of_birth"
                            type="date"
                            v-model="form.date_of_birth"
                        />
                        <InputError :message="errors.date_of_birth" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="preferred_contact_method">Preferred Contact</Label>
                        <Select name="preferred_contact_method" v-model="form.preferred_contact_method">
                            <SelectTrigger id="preferred_contact_method" class="w-full">
                                <SelectValue placeholder="Select..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="email">Email</SelectItem>
                                <SelectItem value="phone">Phone</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.preferred_contact_method" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="notes">Notes</Label>
                        <textarea
                            id="notes"
                            name="notes"
                            v-model="form.notes"
                            rows="3"
                            class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors"
                            placeholder="Additional notes..."
                        ></textarea>
                        <InputError :message="errors.notes" />
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <input
                                    type="hidden"
                                    name="is_primary"
                                    :value="form.is_primary ? '1' : '0'"
                                />
                                <Switch id="is_primary" v-model="form.is_primary" />
                                <Label for="is_primary" class="cursor-pointer"
                                    >Primary Contact</Label
                                >
                            </div>
                            <InputError :message="errors.is_primary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <input
                                    type="hidden"
                                    name="receives_billing"
                                    :value="form.receives_billing ? '1' : '0'"
                                />
                                <Switch
                                    id="receives_billing"
                                    v-model="form.receives_billing"
                                />
                                <Label for="receives_billing" class="cursor-pointer"
                                    >Receives Billing</Label
                                >
                            </div>
                            <InputError :message="errors.receives_billing" />
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
                        {{ contact ? 'Update' : 'Add' }} Contact
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
