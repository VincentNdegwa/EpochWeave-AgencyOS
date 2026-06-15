<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { ClockIcon, ScrollTextIcon } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import WorkspaceInvoiceSettingsController from '@/actions/App/Http/Controllers/WorkspaceSettings/InvoiceController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import NumberingBuilder from '@/pages/workspace-settings/components/NumberingBuilder.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index } from '@/routes/workspace-settings';
import type { InvoiceSettings } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Business settings', href: index() },
            { title: 'Invoices' },
        ],
    },
});

const page = usePage();

const canUpdate = computed(
    () => page.props.canUpdateWorkspaceSettings as boolean,
);
const invoiceSettings = computed(
    () => page.props.invoiceSettings as InvoiceSettings | null,
);

const termsContent = ref('');

watch(
    () => invoiceSettings.value,
    (s) => {
        termsContent.value = s?.default_terms ?? '';
    },
    { immediate: true },
);

const toolbarOptions = [
    [{ header: [1, 2, 3, false] }],
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['link'],
    ['clean'],
];
</script>

<template>
    <Head title="Invoice settings" />
    <h1 class="sr-only">Invoice settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Invoices"
            description="Control numbering, payment terms, and default conditions"
        />

        <Form
            v-bind="WorkspaceInvoiceSettingsController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <fieldset :disabled="!canUpdate" class="space-y-6">
                <input
                    type="hidden"
                    name="default_terms"
                    :value="termsContent"
                />

                <!-- Timeline & Payment Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10"
                        >
                            <ClockIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">
                                Timeline & payment
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Default windows applied to every new invoice
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="payment_due_days">Payment due</Label>
                        <Input
                            id="payment_due_days"
                            name="payment_due_days"
                            type="number"
                            min="0"
                            max="365"
                            :default-value="
                                invoiceSettings?.payment_due_days ?? 7
                            "
                            placeholder="7"
                        />
                        <InputError :message="errors.payment_due_days" />
                    </div>
                </div>

                <!-- Default Terms Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10"
                        >
                            <ScrollTextIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">
                                Default terms & conditions
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Pre-populated in every new invoice's Terms block
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <div
                            class="overflow-hidden rounded-lg border border-border transition-shadow focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-1"
                        >
                            <QuillEditor
                                v-model:content="termsContent"
                                theme="snow"
                                content-type="html"
                                :options="{
                                    modules: { toolbar: toolbarOptions },
                                    placeholder:
                                        'Enter your standard terms — payment conditions, late fees, delivery terms, cancellation policy…',
                                }"
                            />
                        </div>
                        <InputError :message="errors.default_terms" />
                    </div>
                </div>

                <!-- Invoice Numbering Section -->
                <NumberingBuilder
                    :initial-values="invoiceSettings?.numbering"
                    :errors="errors"
                    name-prefix="numbering"
                    preview-label="Next invoice"
                    default-prefix="INV"
                />
            </fieldset>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing || !canUpdate"
                    data-test="update-invoice-settings-button"
                >
                    Save
                </Button>
                <p v-if="!canUpdate" class="text-sm text-muted-foreground">
                    You don't have permission to edit these settings.
                </p>
            </div>
        </Form>
    </div>
</template>
