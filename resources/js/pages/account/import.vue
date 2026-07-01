<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Check, Circle, Dot, Download, Upload } from '@lucide/vue';
import { computed, ref } from 'vue';
import AccountController from '@/actions/App/Http/Controllers/AccountController';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';
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
import type { AccountStatus } from '@/types/enums';

type LookupOption = {
    id: number;
    name?: string;
    label?: string;
};

const props = defineProps<{
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

const statusOptions: { value: AccountStatus; label: string }[] = [
    { value: 'lead', label: 'Lead' },
    { value: 'opportunity', label: 'Opportunity' },
    { value: 'client', label: 'Client' },
    { value: 'archived', label: 'Archived' },
];

const fields = [
    { key: 'company_name', label: 'Company Name', required: true },
    { key: 'phone', label: 'Phone' },
    { key: 'website', label: 'Website' },
    { key: 'description', label: 'Description' },
    { key: 'founded_at', label: 'Founded Date' },
    { key: 'annual_revenue', label: 'Annual Revenue' },
    { key: 'employee_count', label: 'Employee Count' },
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
    },
    {
        step: 2,
        title: 'Upload file',
        description: 'Select your CSV or Excel file',
    },
    {
        step: 3,
        title: 'Preview & map',
        description: 'Match columns and import',
    },
];

const mapping = ref<Record<string, string>>({});
const bulk = ref({
    status: 'lead' as AccountStatus,
    industry_id: '',
    lead_source_id: '',
    company_size_id: '',
});

const mappingForm = useForm<{
    file: File | null;
    mapping: Record<string, string>;
    bulk: {
        status: AccountStatus;
        industry_id: string;
        lead_source_id: string;
        company_size_id: string;
    };
}>({
    file: null,
    mapping: {},
    bulk: {
        status: 'lead',
        industry_id: '',
        lead_source_id: '',
        company_size_id: '',
    },
});

const currentStepMeta = computed(() => steps[currentStep.value - 1]);

const applySuggestedMapping = () => {
    const suggested: Record<string, string> = {};

    for (const field of fields) {
        const match = previewHeaders.value.find((header) => {
            const normalized = header.toLowerCase().replace(/\s+/g, '_');
            return normalized === field.key || header.toLowerCase() === field.label.toLowerCase();
        });

        if (match) {
            suggested[field.key] = match;
        }
    }

    mapping.value = suggested;
};

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
        applySuggestedMapping();
        currentStep.value = 3;
    } catch (e) {
        previewError.value = 'Failed to preview file.';
    } finally {
        previewLoading.value = false;
    }
};

const submitImport = () => {
    if (! file.value) {
        return;
    }

    mappingForm.file = file.value;
    mappingForm.mapping = Object.fromEntries(
        Object.entries(mapping.value).map(([key, value]) => [key, value === 'none' ? '' : value]),
    );
    mappingForm.bulk.status = bulk.value.status;
    mappingForm.bulk.industry_id = bulk.value.industry_id === 'none' ? '' : bulk.value.industry_id;
    mappingForm.bulk.lead_source_id = bulk.value.lead_source_id === 'none' ? '' : bulk.value.lead_source_id;
    mappingForm.bulk.company_size_id = bulk.value.company_size_id === 'none' ? '' : bulk.value.company_size_id;

    mappingForm.post(AccountController.importMethod().url, {
        forceFormData: true,
    });
};

const downloadTemplate = () => {
    window.location.href = '/accounts/import-template';
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
        <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
            <Stepper
                v-model="currentStep"
                orientation="vertical"
                class="mx-auto flex w-full max-w-md flex-col justify-start gap-10"
            >
                <StepperItem
                    v-for="step in steps"
                    :key="step.step"
                    v-slot="{ state }"
                    class="relative flex w-full items-start gap-6"
                    :step="step.step"
                >
                    <StepperSeparator
                        v-if="step.step !== steps[steps.length - 1]?.step"
                        class="absolute left-[18px] top-[38px] block h-[105%] w-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                    />

                    <StepperTrigger as-child>
                        <Button
                            :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'"
                            size="icon"
                            class="z-10 shrink-0 rounded-full"
                            :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
                        >
                            <Check v-if="state === 'completed'" class="size-5" />
                            <Circle v-if="state === 'active'" />
                            <Dot v-if="state === 'inactive'" />
                        </Button>
                    </StepperTrigger>

                    <div class="flex flex-col gap-1">
                        <StepperTitle
                            :class="[state === 'active' && 'text-primary']"
                            class="text-sm font-semibold transition lg:text-base"
                        >
                            {{ step.title }}
                        </StepperTitle>
                        <StepperDescription
                            :class="[state === 'active' && 'text-primary']"
                            class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm"
                        >
                            {{ step.description }}
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
                                company_name, phone, website, description, founded_at,
                                annual_revenue, employee_count, first_name, last_name,
                                email, contact_phone
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
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="space-y-4">
                                <h4 class="font-medium">Column mapping</h4>
                                <div
                                    v-for="field in fields"
                                    :key="field.key"
                                    class="grid grid-cols-2 items-center gap-4"
                                >
                                    <Label :for="`map-${field.key}`">
                                        {{ field.label }}
                                        <span v-if="field.required" class="text-destructive">*</span>
                                    </Label>
                                    <Select
                                        :id="`map-${field.key}`"
                                        v-model="mapping[field.key]"
                                    >
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="— Ignore —" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">— Ignore —</SelectItem>
                                            <SelectItem
                                                v-for="header in previewHeaders"
                                                :key="header"
                                                :value="header"
                                            >
                                                {{ header }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4 class="font-medium">Apply all</h4>
                                <div class="grid gap-4">
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
                        </div>

                        <div v-if="previewRows.length > 0" class="space-y-2">
                            <h4 class="font-medium">Preview</h4>
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead
                                                v-for="header in previewHeaders"
                                                :key="header"
                                            >
                                                {{ header }}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="(row, rowIndex) in previewRows"
                                            :key="rowIndex"
                                        >
                                            <TableCell
                                                v-for="(cell, cellIndex) in row"
                                                :key="cellIndex"
                                            >
                                                {{ cell }}
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Showing first {{ previewRows.length }} rows.
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end gap-2">
                    <Button
                        v-if="currentStep === 1"
                        variant="outline"
                        as-child
                    >
                        <a :href="AccountController.index().url">Cancel</a>
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
                    <Button
                        v-if="currentStep === 3"
                        :disabled="!mapping.company_name || mapping.company_name === 'none' || mappingForm.processing"
                        @click="submitImport"
                    >
                        <Check class="mr-2 h-4 w-4" />
                        {{ mappingForm.processing ? 'Importing...' : 'Import accounts' }}
                    </Button>
                </CardFooter>

                <p
                    v-if="currentStep === 3 && mappingForm.errors.file"
                    class="px-6 pb-6 text-sm text-destructive"
                >
                    {{ mappingForm.errors.file }}
                </p>
            </Card>
        </div>
    </div>
</template>
