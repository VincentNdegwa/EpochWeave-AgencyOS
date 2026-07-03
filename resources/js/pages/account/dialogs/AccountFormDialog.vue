<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus, X } from '@lucide/vue';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import {
    store as accountStore,
    update as accountUpdate,
} from '@/actions/App/Http/Controllers/AccountController';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useBuilderDataStore } from '@/stores/builderData';
import type { Account } from '@/types/models/account';

interface ContactForm {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    job_title: string;
    date_of_birth: string;
    department: string;
    preferred_contact_method: string;
    notes: string;
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

const builderData = useBuilderDataStore();
const { industries, leadSources, companySizes } = storeToRefs(builderData);

const isEditMode = computed(() => !!props.account);

function blankContact(): ContactForm {
    return {
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

const contacts = ref<ContactForm[]>([blankContact()]);
const companyName = ref(props.account?.company_name ?? '');
const phone = ref(props.account?.phone ?? '');
const website = ref(props.account?.website ?? '');
const description = ref(props.account?.description ?? '');
const foundedAt = ref(props.account?.founded_at ?? '');
const industryId = ref<string>((props.account?.industry_id ?? '').toString());
const leadSourceId = ref<string>(
    (props.account?.lead_source_id ?? '').toString(),
);
const companySizeId = ref<string>(
    (props.account?.company_size_id ?? '').toString(),
);
const annualRevenue = ref(props.account?.annual_revenue ?? '');
const employeeCount = ref(props.account?.employee_count ?? '');

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
        phone.value = props.account.phone ?? '';
        website.value = props.account.website ?? '';
        description.value = props.account.description ?? '';
        foundedAt.value = props.account.founded_at ?? '';
        industryId.value = (props.account.industry_id ?? '').toString();
        leadSourceId.value = (props.account.lead_source_id ?? '').toString();
        companySizeId.value = (props.account.company_size_id ?? '').toString();
        annualRevenue.value = props.account.annual_revenue ?? '';
        employeeCount.value = props.account.employee_count ?? '';

        return;
    }

    companyName.value = '';
    phone.value = '';
    website.value = '';
    description.value = '';
    foundedAt.value = '';
    industryId.value = '';
    leadSourceId.value = '';
    companySizeId.value = '';
    annualRevenue.value = '';
    employeeCount.value = '';
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

        builderData.fetchIndustries();
        builderData.fetchLeadSources();
        builderData.fetchCompanySizes();

        resetPrimaryFields();

        if (!props.account) {
            contacts.value = [blankContact()];
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent
            class="max-h-[90vh] overflow-hidden sm:max-w-xl md:max-w-2xl"
        >
            <DialogHeader>
                <DialogTitle>{{
                    isEditMode ? 'Edit Account' : 'New Account'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEditMode
                            ? 'Update account information.'
                            : 'Add a new client account to your workspace.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="formAction as any"
                :options="{ preserveScroll: true, preserveState: true }"
                @success="
                    emit('success');
                    emit('update:open', false);
                "
                v-slot="{ errors, processing }"
            >
                <div
                    class="grid max-h-[calc(90vh-180px)] gap-6 overflow-y-auto py-4 pr-2"
                >
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
                                placeholder="Enter company name..."
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

                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                name="phone"
                                v-model="phone"
                                placeholder="+1 (555) 123-4567"
                            />
                            <InputError :message="errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="founded_at">Founded</Label>
                            <Input
                                id="founded_at"
                                name="founded_at"
                                type="date"
                                v-model="foundedAt"
                            />
                            <InputError :message="errors.founded_at" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="industry_id">Industry</Label>
                            <Select name="industry_id" v-model="industryId">
                                <SelectTrigger id="industry_id" class="w-full">
                                    <SelectValue
                                        placeholder="Select industry..."
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="industry in industries"
                                        :key="industry.id"
                                        :value="industry.id.toString()"
                                    >
                                        {{ industry.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.industry_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="lead_source_id">Lead Source</Label>
                            <Select
                                name="lead_source_id"
                                v-model="leadSourceId"
                            >
                                <SelectTrigger
                                    id="lead_source_id"
                                    class="w-full"
                                >
                                    <SelectValue
                                        placeholder="Select source..."
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="source in leadSources"
                                        :key="source.id"
                                        :value="source.id.toString()"
                                    >
                                        {{ source.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.lead_source_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="company_size_id">Company Size</Label>
                            <Select
                                name="company_size_id"
                                v-model="companySizeId"
                            >
                                <SelectTrigger
                                    id="company_size_id"
                                    class="w-full"
                                >
                                    <SelectValue placeholder="Select size..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="size in companySizes"
                                        :key="size.id"
                                        :value="size.id.toString()"
                                    >
                                        {{ size.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.company_size_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="annual_revenue">Annual Revenue</Label>
                            <Input
                                id="annual_revenue"
                                name="annual_revenue"
                                type="number"
                                v-model="annualRevenue"
                                placeholder="0"
                            />
                            <InputError :message="errors.annual_revenue" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="employee_count">Employees</Label>
                            <Input
                                id="employee_count"
                                name="employee_count"
                                type="number"
                                v-model="employeeCount"
                                placeholder="0"
                            />
                            <InputError :message="errors.employee_count" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                name="description"
                                v-model="description"
                                rows="3"
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors"
                                placeholder="Brief company description..."
                            ></textarea>
                            <InputError :message="errors.description" />
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
                                        placeholder="Enter first name..."
                                    />
                                    <InputError
                                        :message="
                                            errors[
                                                `contacts.${index}.first_name`
                                            ]
                                        "
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
                                        placeholder="Enter last name..."
                                    />
                                    <InputError
                                        :message="
                                            errors[
                                                `contacts.${index}.last_name`
                                            ]
                                        "
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
                                        placeholder="contact@company.com"
                                    />
                                    <InputError
                                        :message="
                                            errors[`contacts.${index}.email`]
                                        "
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
                                        :message="
                                            errors[`contacts.${index}.phone`]
                                        "
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
                                        placeholder="Enter job title..."
                                    />
                                    <InputError
                                        :message="
                                            errors[
                                                `contacts.${index}.job_title`
                                            ]
                                        "
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
                                            :value="
                                                contact.is_primary ? '1' : '0'
                                            "
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
                                            :value="
                                                contact.receives_billing
                                                    ? '1'
                                                    : '0'
                                            "
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
