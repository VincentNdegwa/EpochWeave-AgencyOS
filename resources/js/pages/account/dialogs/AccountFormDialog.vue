<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { store as accountStore, update as accountUpdate } from '@/actions/App/Http/Controllers/AccountController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import type { Account } from '@/types/models/account';

interface ContactForm {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    job_title: string;
    is_primary: boolean;
    receives_billing: boolean;
}

interface Props {
    open: boolean;
    account?: Account;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const isEditMode = computed(() => !!props.account);

function blankContact(): ContactForm {
    return {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        job_title: '',
        is_primary: false,
        receives_billing: false,
    };
}

const contacts = ref<ContactForm[]>([blankContact()]);
const companyName = ref(props.account?.company_name ?? '');
const website = ref(props.account?.website ?? '');

const addContact = () => {
    contacts.value.push(blankContact());
};

const removeContact = (index: number) => {
    contacts.value.splice(index, 1);
};

const formAction = computed(() => {
    if (isEditMode.value && props.account) {
        return accountUpdate.form({ account: props.account.id });
    }

    return accountStore.form();
});

const resetPrimaryFields = () => {
    if (props.account) {
        companyName.value = props.account.company_name ?? '';
        website.value = props.account.website ?? '';

        return;
    }

    companyName.value = '';
    website.value = '';
};

watch(
    () => props.account,
    () => {
        resetPrimaryFields();
    },
    { immediate: true },
);

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }

        resetPrimaryFields();

        if (!props.account) {
            contacts.value = [blankContact()];
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl md:max-w-2xl max-h-[90vh] overflow-hidden">
            <DialogHeader>
                <DialogTitle>{{ isEditMode ? 'Edit Account' : 'New Account' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditMode ? 'Update account information.' : 'Add a new client account to your workspace.' }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="(formAction as any)"
                :options="{ preserveScroll: true, preserveState: true }"
                @success="emit('success'); emit('update:open', false)"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 py-4 overflow-y-auto max-h-[calc(90vh-180px)] pr-2">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="company_name" required>
                                Company Name
                            </Label>
                            <Input
                                id="company_name"
                                name="company_name"
                                required
                                v-model="companyName"
                                placeholder="Acme Corporation"
                            />
                            <InputError :message="errors.company_name" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="website">Website</Label>
                            <Input
                                id="website"
                                name="website"
                                type="url"
                                v-model="website"
                                placeholder="https://example.com"
                            />
                            <InputError :message="errors.website" />
                        </div>
                    </div>

                    <div v-if="!isEditMode" class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="grid gap-0.5">
                                <div class="text-sm font-semibold">
                                    Contacts
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Add one or more contacts for this account.
                                </div>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addContact"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Add Contact
                            </Button>
                        </div>

                        <div
                            v-for="(contact, index) in contacts"
                            :key="index"
                            class="space-y-4 rounded-lg border border-border bg-muted/30 p-4"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="text-sm font-medium">
                                    Contact {{ index + 1 }}
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeContact(index)"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label
                                        :for="`contacts.${index}.first_name`"
                                        required
                                    >
                                        First Name
                                    </Label>
                                    <Input
                                        :id="`contacts.${index}.first_name`"
                                        :name="`contacts.${index}.first_name`"
                                        v-model="contact.first_name"
                                        required
                                        placeholder="John"
                                    />
                                    <InputError
                                        :message="errors[`contacts.${index}.first_name`]"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label
                                        :for="`contacts.${index}.last_name`"
                                        required
                                    >
                                        Last Name
                                    </Label>
                                    <Input
                                        :id="`contacts.${index}.last_name`"
                                        :name="`contacts.${index}.last_name`"
                                        v-model="contact.last_name"
                                        required
                                        placeholder="Doe"
                                    />
                                    <InputError
                                        :message="errors[`contacts.${index}.last_name`]"
                                    />
                                </div>

                                <div class="grid gap-2 md:col-span-2">
                                    <Label
                                        :for="`contacts.${index}.email`"
                                        required
                                    >
                                        Email
                                    </Label>
                                    <Input
                                        :id="`contacts.${index}.email`"
                                        :name="`contacts.${index}.email`"
                                        type="email"
                                        v-model="contact.email"
                                        required
                                        placeholder="john@example.com"
                                    />
                                    <InputError
                                        :message="errors[`contacts.${index}.email`]"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label :for="`contacts.${index}.phone`">
                                        Phone
                                    </Label>
                                    <Input
                                        :id="`contacts.${index}.phone`"
                                        :name="`contacts.${index}.phone`"
                                        v-model="contact.phone"
                                        placeholder="+1 (555) 123-4567"
                                    />
                                    <InputError
                                        :message="errors[`contacts.${index}.phone`]"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label :for="`contacts.${index}.job_title`">
                                        Job Title
                                    </Label>
                                    <Input
                                        :id="`contacts.${index}.job_title`"
                                        :name="`contacts.${index}.job_title`"
                                        v-model="contact.job_title"
                                        placeholder="CEO"
                                    />
                                    <InputError
                                        :message="errors[`contacts.${index}.job_title`]"
                                    />
                                </div>

                                <div
                                    class="flex items-center justify-between gap-4 rounded-lg border border-border bg-background px-3 py-2 md:col-span-2"
                                >
                                    <Label
                                        :for="`contacts.${index}.is_primary`"
                                        class="cursor-pointer"
                                    >
                                        Primary Contact
                                    </Label>
                                    <div class="flex items-center gap-3">
                                        <Checkbox
                                            :id="`contacts.${index}.is_primary`"
                                            v-model="contact.is_primary"
                                        />
                                        <input
                                            type="hidden"
                                            :name="`contacts.${index}.is_primary`"
                                            :value="contact.is_primary ? '1' : '0'"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between gap-4 rounded-lg border border-border bg-background px-3 py-2 md:col-span-2"
                                >
                                    <Label
                                        :for="`contacts.${index}.receives_billing`"
                                        class="cursor-pointer"
                                    >
                                        Receives Billing
                                    </Label>
                                    <div class="flex items-center gap-3">
                                        <Checkbox
                                            :id="`contacts.${index}.receives_billing`"
                                            v-model="contact.receives_billing"
                                        />
                                        <input
                                            type="hidden"
                                            :name="`contacts.${index}.receives_billing`"
                                            :value="contact.receives_billing ? '1' : '0'"
                                        />
                                    </div>
                                </div>
                            </div>
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
                        {{ isEditMode ? 'Update Account' : 'Create Account' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
