<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Form } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Plus, X } from '@lucide/vue';
import { ref } from 'vue';
import { store as accountStore } from '@/actions/App/Http/Controllers/AccountController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';

const { values: accountStatuses } = useAccountStatuses();

const contacts = ref([
    {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        job_title: '',
        is_primary: false,
        receives_billing: false,
    },
]);

const addContact = () => {
    contacts.value.push({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        job_title: '',
        is_primary: false,
        receives_billing: false,
    });
};

const removeContact = (index: number) => {
    contacts.value.splice(index, 1);
};

defineOptions({
    layout: {
        title: 'Create Account',
        description: 'Add a new client account',
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Accounts',
                href: accountIndex(),
            },
            {
                title: 'Create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Create Account" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">
                    Create Account
                </h1>
                <p class="text-muted-foreground">
                    Add a new client account to your workspace.
                </p>
            </div>
        </div>

        <!-- Form -->
        <Form
            v-bind="accountStore.form()"
            v-slot="{ errors, processing }"
            class="max-w-4xl space-y-8"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="company_name" required>Company Name</Label>
                    <Input
                        id="company_name"
                        name="company_name"
                        required
                        placeholder="Acme Corporation"
                    />
                    <InputError :message="errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="website">Website</Label>
                    <Input
                        id="website"
                        name="website"
                        type="url"
                        placeholder="https://example.com"
                    />
                    <InputError :message="errors.website" />
                </div>

                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <Select name="status">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="status in accountStatuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.status" />
                </div>
            </div>

            <!-- Contacts Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Contacts</h3>
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
                    class="space-y-4 rounded-lg border p-4"
                >
                    <div class="flex justify-end">
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
                                >First Name</Label
                            >
                            <Input
                                :id="`contacts.${index}.first_name`"
                                :name="`contacts.${index}.first_name`"
                                v-model="contact.first_name"
                                required
                            />
                            <InputError
                                :message="
                                    errors[`contacts.${index}.first_name`]
                                "
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`contacts.${index}.last_name`" required
                                >Last Name</Label
                            >
                            <Input
                                :id="`contacts.${index}.last_name`"
                                :name="`contacts.${index}.last_name`"
                                v-model="contact.last_name"
                                required
                            />
                            <InputError
                                :message="errors[`contacts.${index}.last_name`]"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`contacts.${index}.email`" required
                                >Email</Label
                            >
                            <Input
                                :id="`contacts.${index}.email`"
                                :name="`contacts.${index}.email`"
                                type="email"
                                v-model="contact.email"
                                required
                            />
                            <InputError
                                :message="errors[`contacts.${index}.email`]"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`contacts.${index}.phone`"
                                >Phone</Label
                            >
                            <Input
                                :id="`contacts.${index}.phone`"
                                :name="`contacts.${index}.phone`"
                                v-model="contact.phone"
                            />
                            <InputError
                                :message="errors[`contacts.${index}.phone`]"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`contacts.${index}.job_title`"
                                >Job Title</Label
                            >
                            <Input
                                :id="`contacts.${index}.job_title`"
                                :name="`contacts.${index}.job_title`"
                                v-model="contact.job_title"
                            />
                            <InputError
                                :message="errors[`contacts.${index}.job_title`]"
                            />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                :id="`contacts.${index}.is_primary`"
                                v-model="contact.is_primary"
                            />
                            <Label :for="`contacts.${index}.is_primary`"
                                >Primary Contact</Label
                            >
                            <input
                                type="hidden"
                                :name="`contacts.${index}.is_primary`"
                                :value="contact.is_primary ? '1' : '0'"
                            />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                :id="`contacts.${index}.receives_billing`"
                                v-model="contact.receives_billing"
                            />
                            <Label :for="`contacts.${index}.receives_billing`"
                                >Receives Billing</Label
                            >
                            <input
                                type="hidden"
                                :name="`contacts.${index}.receives_billing`"
                                :value="contact.receives_billing ? '1' : '0'"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <Link :href="accountIndex().url">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
                <Button type="submit" :disabled="processing"
                    >Create Account</Button
                >
            </div>
        </Form>
    </div>
</template>
