<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Check, Download, Upload } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import {
    downloadImportTemplate,
    importMethod as accountImport,
    index as accountIndexRoute,
} from '@/actions/App/Http/Controllers/AccountController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Stepper,
    StepperDescription,
    StepperIndicator,
    StepperItem,
    StepperSeparator,
    StepperTitle,
    StepperTrigger,
} from '@/components/ui/stepper';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';
import type { AccountStatus } from '@/types/enums';

type LookupOption = {
    id: number;
    name?: string;
    label?: string;
};

type AccountImportRow = {
    company_name: string;
    phone: string;
    website: string;
    contact_first_name: string;
    contact_last_name: string;
    contact_email: string;
    contact_phone: string;
    status: AccountStatus;
    industry_id: string;
    lead_source_id: string;
    company_size_id: string;
};

defineProps<{
    industries: LookupOption[];
    lead_sources: LookupOption[];
    company_sizes: LookupOption[];
}>();

const currentStep = ref(1);
const file = ref<File | null>(null);
const previewHeaders = ref<string[]>([]);
const previewRows = ref<string[][]>([]);
const previewLoading = ref(false);
const previewError = ref('');
const accounts = ref<AccountImportRow[]>([]);

const statusOptions: { value: AccountStatus; label: string }[] = [
    { value: 'lead', label: 'Lead' },
    { value: 'opportunity', label: 'Opportunity' },
    { value: 'client', label: 'Client' },
    { value: 'archived', label: 'Archived' },
];

const fields: { key: keyof AccountImportRow; label: string; required?: boolean }[] = [
    { key: 'company_name', label: 'Company Name', required: true },
    { key: 'phone', label: 'Phone' },
    { key: 'website', label: 'Website' },
    { key: 'contact_first_name', label: 'Contact First Name' },
    { key: 'contact_last_name', label: 'Contact Last Name' },
    { key: 'contact_email', label: 'Contact Email' },
    { key: 'contact_phone', label: 'Contact Phone' },
];

const steps = [
    {
        step: 1,
        title: 'Download template',
        description: 'Get the spreadsheet format',
        icon: Download,
    },
    {
        step: 2,
        title: 'Upload file',
        description: 'Select your CSV or Excel file',
        icon: Upload,
    },
    {
        step: 3,
        title: 'Preview & map',
        description: 'Match columns and import',
        icon: Check,
    },
];

const bulk = ref({
    status: 'lead' as AccountStatus,
    industry_id: '',
    lead_source_id: '',
    company_size_id: '',
});

const currentStepMeta = computed(() => steps[currentStep.value - 1]);

const formAction = computed(() => accountImport.form());

const defaultAccount = (): AccountImportRow => ({
    company_name: '',
    phone: '',
    website: '',
    contact_first_name: '',
    contact_last_name: '',
    contact_email: '',
    contact_phone: '',
    status: 'lead',
    industry_id: '',
    lead_source_id: '',
    company_size_id: '',
});

const buildAccounts = (headers: string[], rows: string[][]): AccountImportRow[] => {
    const headerIndex = headers.reduce<Record<string, number>>((acc, header, index) => {
        acc[header.toLowerCase().trim()] = index;

        return acc;
    }, {});

    return rows.map((row) => {
        const account = defaultAccount();

        for (const field of fields) {
            const index = headerIndex[field.key];

            if (index !== undefined) {
                (account[field.key as keyof AccountImportRow] as string) = row[index] ?? '';
            }
        }

        return account;
    });
};

watch(() => bulk.value.status, (value) => {
    accounts.value.forEach((account) => {
        account.status = value;
    });
});

watch(() => bulk.value.industry_id, (value) => {
    accounts.value.forEach((account) => {
        account.industry_id = value;
    });
});

watch(() => bulk.value.lead_source_id, (value) => {
    accounts.value.forEach((account) => {
        account.lead_source_id = value;
    });
});

watch(() => bulk.value.company_size_id, (value) => {
    accounts.value.forEach((account) => {
        account.company_size_id = value;
    });
});

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    file.value = input.files?.[0] ?? null;
    previewError.value = '';
    previewHeaders.value = [];
    previewRows.value = [];
};

const csrfToken = () => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
};

const loadPreview = async () => {
    if (! file.value) {
        return;
    }

    previewLoading.value = true;
    previewError.value = '';

    try {
        const formData = new FormData();
        formData.append('file', file.value);

        const response = await fetch('/accounts/import-preview', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': csrfToken(),
            },
        });

        if (! response.ok) {
            const error = await response.json();
            previewError.value = error.message || 'Failed to preview file.';

            return;
        }

        const data = await response.json();
        previewHeaders.value = data.headers;
        previewRows.value = data.rows;
        accounts.value = buildAccounts(data.headers, data.rows);
        currentStep.value = 3;
    } catch {
        previewError.value = 'Failed to preview file.';
    } finally {
        previewLoading.value = false;
    }
};

const anyAccountMissingCompanyName = computed(
    () => accounts.value.length === 0 || accounts.value.some((a) => !a.company_name.trim()),
);

const downloadTemplate = () => {
    window.location.href = downloadImportTemplate().url;
};

defineOptions({
    layout: {
        title: 'Import Accounts',
        description: 'Import accounts and contacts from a spreadsheet.',
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
                title: 'Import Accounts',
            },
        ],
    },
});
</script>

<template>
    <Head title="Import Accounts" />

    <div class="space-y-6">
        <div class="space-y-6">
            <Stepper v-model="currentStep" class="flex items-start gap-2">
                <StepperItem
                    v-for="item in steps"
                    :key="item.step"
                    :step="item.step"
                    class="relative flex w-full flex-col items-center justify-center"
                >
                    <StepperTrigger>
                        <StepperIndicator v-slot="{ step }" class="bg-muted">
                            <template v-if="item.icon">
                                <component :is="item.icon" class="h-4 w-4" />
                            </template>
                            <span v-else>{{ step }}</span>
                        </StepperIndicator>
                    </StepperTrigger>

                    <StepperSeparator
                        v-if="item.step !== steps[steps.length - 1]?.step"
                        class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                    />

                    <div class="flex flex-col items-center">
                        <StepperTitle>
                            {{ item.title }}
                        </StepperTitle>
                        <StepperDescription>
                            {{ item.description }}
                        </StepperDescription>
                    </div>
                </StepperItem>
            </Stepper>

            <Card>
                <CardHeader>
                    <CardTitle>{{ currentStepMeta.title }}</CardTitle>
                    <CardDescription>{{ currentStepMeta.description }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div v-if="currentStep === 1" class="space-y-4">
                        <p class="text-sm text-muted-foreground">
                            The spreadsheet must contain account details and contact details.
                            Optional values like industry, lead source, and company size are
                            applied in the next step instead of being imported from the file.
                        </p>

                        <div class="rounded-md bg-muted p-4">
                            <p class="mb-2 text-sm font-medium">Expected columns</p>
                            <p class="text-sm text-muted-foreground">
                                company_name, phone, website, contact_first_name,
                                contact_last_name, contact_email, contact_phone
                            </p>
                            <p class="mt-2 text-xs text-muted-foreground">
                                The downloaded template is an XLSX file.
                            </p>
                        </div>
                    </div>

                    <div v-if="currentStep === 2" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="import-file">Spreadsheet</Label>
                            <Input
                                id="import-file"
                                type="file"
                                accept=".csv,.xlsx,.xls"
                                @change="handleFileChange"
                            />
                            <p class="text-xs text-muted-foreground">
                                Supported formats: CSV, XLSX, XLS.
                            </p>
                        </div>

                        <p v-if="previewError" class="text-sm text-destructive">
                            {{ previewError }}
                        </p>
                    </div>

                    <div v-if="currentStep === 3" class="space-y-6">
                        <div class="rounded-md border p-4">
                            <h4 class="mb-4 font-medium">Apply all</h4>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="grid gap-2">
                                    <Label for="bulk-status">Status</Label>
                                    <Select id="bulk-status" v-model="bulk.status">
                                        <SelectTrigger class="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="option in statusOptions"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="bulk-industry">Industry</Label>
                                    <Select id="bulk-industry" v-model="bulk.industry_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="— None —" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">— None —</SelectItem>
                                            <SelectItem
                                                v-for="option in industries"
                                                :key="option.id"
                                                :value="option.id.toString()"
                                            >
                                                {{ option.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="bulk-lead-source">Lead Source</Label>
                                    <Select id="bulk-lead-source" v-model="bulk.lead_source_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="— None —" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">— None —</SelectItem>
                                            <SelectItem
                                                v-for="option in lead_sources"
                                                :key="option.id"
                                                :value="option.id.toString()"
                                            >
                                                {{ option.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="bulk-company-size">Company Size</Label>
                                    <Select id="bulk-company-size" v-model="bulk.company_size_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="— None —" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">— None —</SelectItem>
                                            <SelectItem
                                                v-for="option in company_sizes"
                                                :key="option.id"
                                                :value="option.id.toString()"
                                            >
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </div>

                        <Form
                            id="import-accounts-form"
                            v-bind="formAction as any"
                            :options="{ preserveScroll: true, preserveState: true }"
                            v-slot="{ errors, processing }"
                        >
                            <div v-if="accounts.length > 0" class="space-y-2">
                                <h4 class="font-medium">Preview & edit</h4>
                                <div class="overflow-x-auto rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead v-for="field in fields" :key="field.key">
                                                    <Label :required="!!field.required">{{ field.label }}</Label>
                                                </TableHead>
                                                <TableHead>
                                                    <Label required>Status</Label>
                                                </TableHead>
                                                <TableHead>
                                                    <Label>Industry</Label>
                                                </TableHead>
                                                <TableHead>
                                                    <Label>Lead Source</Label>
                                                </TableHead>
                                                <TableHead>
                                                    <Label>Company Size</Label>
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="(account, rowIndex) in accounts" :key="rowIndex">
                                                <TableCell v-for="field in fields" :key="field.key">
                                                    <Input
                                                        :name="`accounts[${rowIndex}][${field.key}]`"
                                                        v-model="account[field.key]"
                                                        type="text"
                                                        class="min-w-[140px]"
                                                        :placeholder="field.label"
                                                        :required="!!field.required"
                                                    />
                                                    <InputError :message="errors[`accounts.${rowIndex}.${field.key}`]" />
                                                </TableCell>
                                                <TableCell>
                                                    <Select :name="`accounts[${rowIndex}][status]`" v-model="account.status">
                                                        <SelectTrigger class="w-[130px]">
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem
                                                                v-for="option in statusOptions"
                                                                :key="option.value"
                                                                :value="option.value"
                                                            >
                                                                {{ option.label }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError :message="errors[`accounts.${rowIndex}.status`]" />
                                                </TableCell>
                                                <TableCell>
                                                    <Select :name="`accounts[${rowIndex}][industry_id]`" v-model="account.industry_id">
                                                        <SelectTrigger class="w-[130px]">
                                                            <SelectValue placeholder="— None —" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="none">— None —</SelectItem>
                                                            <SelectItem
                                                                v-for="option in industries"
                                                                :key="option.id"
                                                                :value="option.id.toString()"
                                                            >
                                                                {{ option.name }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError :message="errors[`accounts.${rowIndex}.industry_id`]" />
                                                </TableCell>
                                                <TableCell>
                                                    <Select :name="`accounts[${rowIndex}][lead_source_id]`" v-model="account.lead_source_id">
                                                        <SelectTrigger class="w-[130px]">
                                                            <SelectValue placeholder="— None —" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="none">— None —</SelectItem>
                                                            <SelectItem
                                                                v-for="option in lead_sources"
                                                                :key="option.id"
                                                                :value="option.id.toString()"
                                                            >
                                                                {{ option.name }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError :message="errors[`accounts.${rowIndex}.lead_source_id`]" />
                                                </TableCell>
                                                <TableCell>
                                                    <Select :name="`accounts[${rowIndex}][company_size_id]`" v-model="account.company_size_id">
                                                        <SelectTrigger class="w-[130px]">
                                                            <SelectValue placeholder="— None —" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="none">— None —</SelectItem>
                                                            <SelectItem
                                                                v-for="option in company_sizes"
                                                                :key="option.id"
                                                                :value="option.id.toString()"
                                                            >
                                                                {{ option.label }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError :message="errors[`accounts.${rowIndex}.company_size_id`]" />
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Showing first {{ accounts.length }} rows.
                                </p>
                            </div>

                            <div class="flex justify-end pt-4">
                                <Button
                                    type="submit"
                                    :disabled="anyAccountMissingCompanyName || processing"
                                >
                                    <Check class="mr-2 h-4 w-4" />
                                    {{ processing ? 'Importing...' : 'Import accounts' }}
                                </Button>
                            </div>
                        </Form>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end gap-2">
                    <Button
                        v-if="currentStep === 1"
                        variant="outline"
                        as-child
                    >
                        <a :href="accountIndexRoute().url">Cancel</a>
                    </Button>
                    <Button
                        v-if="currentStep > 1"
                        variant="outline"
                        @click="currentStep--"
                    >
                        Back
                    </Button>

                    <Button
                        v-if="currentStep === 1"
                        variant="outline"
                        @click="downloadTemplate"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Download template
                    </Button>
                    <Button
                        v-if="currentStep === 1"
                        @click="currentStep = 2"
                    >
                        I already have a spreadsheet
                    </Button>
                    <Button
                        v-if="currentStep === 2"
                        :disabled="!file || previewLoading"
                        @click="loadPreview"
                    >
                        <Upload class="mr-2 h-4 w-4" />
                        {{ previewLoading ? 'Previewing...' : 'Preview & map' }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>
