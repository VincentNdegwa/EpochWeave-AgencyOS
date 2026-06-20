<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import {
    UserIcon,
    ClockIcon,
    BanknoteIcon,
    ScrollTextIcon,
    SparklesIcon,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import WorkspaceProposalSettingsController from '@/actions/App/Http/Controllers/WorkspaceSettings/ProposalController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import NumberingBuilder from '@/pages/workspace-settings/components/NumberingBuilder.vue';
import { dashboard } from '@/routes';
import { index } from '@/routes/workspace-settings';
import type { ProposalSettings } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Business settings', href: index() },
            { title: 'Proposals' },
        ],
    },
});

const page = usePage();

const canUpdate = computed(
    () => page.props.canUpdateWorkspaceSettings as boolean,
);
const proposalSettings = computed(
    () => page.props.proposalSettings as ProposalSettings | null,
);

// ── Reactive local state ──────────────────────────────────────
const autoArchive = ref(false);
const termsContent = ref('');

watch(
    () => proposalSettings.value,
    (s) => {
        autoArchive.value = Boolean(s?.auto_archive);
        termsContent.value = s?.default_terms ?? '';
    },
    { immediate: true },
);

// Quill toolbar for terms
const toolbarOptions = [
    [{ header: [1, 2, 3, false] }],
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['link'],
    ['clean'],
];
</script>

<template>
    <Head title="Proposal settings" />
    <h1 class="sr-only">Proposal settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Proposals"
            description="Control numbering, defaults, and sender details"
        />

        <Form
            v-bind="WorkspaceProposalSettingsController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <fieldset :disabled="!canUpdate" class="space-y-6">
                <input
                    type="hidden"
                    name="auto_archive"
                    :value="autoArchive ? '1' : '0'"
                />
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
                                Default windows applied to every new proposal
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="default_validity_days"
                                >Validity window</Label
                            >
                            <Input
                                id="default_validity_days"
                                name="default_validity_days"
                                type="number"
                                min="0"
                                max="365"
                                :default-value="
                                    proposalSettings?.default_validity_days ??
                                    14
                                "
                                placeholder="14"
                            />
                            <InputError
                                :message="errors.default_validity_days"
                            />
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
                                    proposalSettings?.payment_due_days ?? 7
                                "
                                placeholder="7"
                            />
                            <InputError :message="errors.payment_due_days" />
                        </div>
                    </div>
                </div>

                <!-- Default Deposit Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10"
                        >
                            <BanknoteIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">
                                Default deposit
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Pre-filled when a new proposal is created
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="default_deposit_percentage"
                            >Deposit percentage</Label
                        >
                        <Input
                            id="default_deposit_percentage"
                            name="default_deposit_percentage"
                            type="number"
                            min="0"
                            max="100"
                            :default-value="
                                proposalSettings?.default_deposit_percentage ??
                                50
                            "
                            placeholder="50"
                        />
                        <InputError
                            :message="errors.default_deposit_percentage"
                        />
                    </div>
                </div>

                <!-- Sender Details Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10"
                        >
                            <UserIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">
                                Sender details
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Appears on the proposal cover and email
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="sender_name">Full name</Label>
                            <Input
                                id="sender_name"
                                name="sender_name"
                                :default-value="
                                    proposalSettings?.sender_name ?? ''
                                "
                                placeholder="e.g. Jane Doe"
                            />
                            <InputError :message="errors.sender_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="sender_title">Job title</Label>
                            <Input
                                id="sender_title"
                                name="sender_title"
                                :default-value="
                                    proposalSettings?.sender_title ?? ''
                                "
                                placeholder="e.g. Director of Delivery"
                            />
                            <InputError :message="errors.sender_title" />
                        </div>
                    </div>
                </div>

                <!-- Behaviour Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10"
                        >
                            <SparklesIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">
                                Behaviour
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Automation rules for proposal lifecycle
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg border p-4"
                    >
                        <div class="space-y-0.5">
                            <Label for="auto_archive"
                                >Auto-archive expired proposals</Label
                            >
                            <p class="text-sm text-muted-foreground">
                                Move proposals out of active views once they
                                pass the validity window
                            </p>
                        </div>
                        <Switch id="auto_archive" v-model="autoArchive" />
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
                                Pre-populated in every new proposal's Terms
                                block
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
                                        'Enter your standard terms — scope limitations, payment conditions, IP ownership, confidentiality…',
                                }"
                            />
                        </div>
                        <InputError :message="errors.default_terms" />
                    </div>
                </div>

                <!-- Proposal Numbering Section -->
                <NumberingBuilder
                    :initial-values="proposalSettings?.numbering"
                    :errors="errors"
                    name-prefix="numbering"
                    preview-label="Next proposal"
                    default-prefix="PROP"
                />
            </fieldset>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing || !canUpdate"
                    data-test="update-proposal-settings-button"
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
