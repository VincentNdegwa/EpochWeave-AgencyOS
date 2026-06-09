<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import WorkspaceProposalSettingsController from '@/actions/App/Http/Controllers/WorkspaceSettings/ProposalController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { dashboard } from '@/routes';
import { index } from '@/routes/workspace-settings';
import type { ProposalSettings } from '@/types';
import {
    FileTextIcon,
    HashIcon,
    UserIcon,
    ClockIcon,
    BanknoteIcon,
    ArchiveIcon,
    ScrollTextIcon,
    EyeIcon,
    SparklesIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XIcon,
    PlusIcon,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard',         href: dashboard() },
            { title: 'Business settings', href: index() },
            { title: 'Proposals' },
        ],
    },
});

const page = usePage();

const canUpdate = computed(() => page.props.canUpdateWorkspaceSettings as boolean);
const proposalSettings = computed(() => page.props.proposalSettings as ProposalSettings | null);

// ── Reactive local state ──────────────────────────────────────
const autoArchive  = ref(false);
const termsContent = ref('');

// ── Numbering builder state ───────────────────────────────────
const localPrefix    = ref('PROP');
const localDelimiter = ref('-');
const localPadding   = ref(7);
const localNextSeq   = ref(1);
const formatTokens   = ref<string[]>([]);

function parseFormatString(format: string): string[] {
    return format.match(/\{[A-Z]+\}/g) ?? [];
}

watch(
    () => proposalSettings.value,
    (s) => {
        autoArchive.value  = Boolean(s?.auto_archive);
        termsContent.value = s?.default_terms ?? '';
        const n = s?.numbering ?? {
            format:               '{PREFIX}{DELIMITER}{YEAR}{DELIMITER}{SEQUENCE}',
            prefix:               'PROP',
            delimiter:            '-',
            sequence_padding:     7,
            next_sequence_number: 1,
        };
        localPrefix.value    = n.prefix;
        localDelimiter.value = n.delimiter;
        localPadding.value   = n.sequence_padding;
        localNextSeq.value   = n.next_sequence_number;
        formatTokens.value   = parseFormatString(n.format);
    },
    { immediate: true },
);

// ── Numbering builder ──────────────────────────────────────────
const computedFormatString = computed(() => formatTokens.value.join(''));

const numberPreview = computed(() => {
    const padded = String(localNextSeq.value).padStart(localPadding.value, '0');
    return computedFormatString.value
        .replace('{PREFIX}',     localPrefix.value || 'PREFIX')
        .replace(/{DELIMITER}/g, localDelimiter.value || '-')
        .replace('{YEAR}',       new Date().getFullYear().toString())
        .replace('{SEQUENCE}',   padded);
});

const tokenDefs = [
    {
        token: '{PREFIX}',
        label: 'PREFIX',
        description: 'Your configured prefix',
        example: 'PROP',
        unique: true,
        pillClass: 'bg-primary/10 text-primary border-primary/30',
        addClass: 'hover:bg-primary/15',
    },
    {
        token: '{YEAR}',
        label: 'YEAR',
        description: 'Current year',
        example: '2026',
        unique: true,
        pillClass: 'bg-blue-500/10 text-blue-600 border-blue-200',
        addClass: 'hover:bg-blue-500/15',
    },
    {
        token: '{DELIMITER}',
        label: 'DELIM',
        description: 'Separator between parts',
        example: '-',
        unique: false,
        pillClass: 'bg-muted text-muted-foreground border-border',
        addClass: 'hover:bg-muted/80',
    },
    {
        token: '{SEQUENCE}',
        label: 'SEQ',
        description: 'Auto-incrementing counter',
        example: '0042',
        unique: true,
        pillClass: 'bg-emerald-500/10 text-emerald-600 border-emerald-200',
        addClass: 'hover:bg-emerald-500/15',
    },
];

function getTokenDef(token: string) {
    return tokenDefs.find((t) => t.token === token);
}

function getTokenPreview(token: string): string {
    if (token === '{PREFIX}')    return localPrefix.value || 'PREFIX';
    if (token === '{DELIMITER}') return localDelimiter.value || '—';
    if (token === '{YEAR}')      return new Date().getFullYear().toString();
    if (token === '{SEQUENCE}')  return String(localNextSeq.value).padStart(localPadding.value, '0');
    return token;
}

function isTokenDisabled(token: string): boolean {
    const def = getTokenDef(token);
    if (!def?.unique) return false;
    return formatTokens.value.includes(token);
}

function addToken(token: string) {
    if (isTokenDisabled(token)) return;
    formatTokens.value = [...formatTokens.value, token];
}

function removeToken(index: number) {
    formatTokens.value = formatTokens.value.filter((_, i) => i !== index);
}

function moveToken(index: number, direction: -1 | 1) {
    const arr = [...formatTokens.value];
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= arr.length) return;
    [arr[index], arr[newIndex]] = [arr[newIndex], arr[index]];
    formatTokens.value = arr;
}

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
            <!-- Hidden field for auto_archive -->
            <input type="hidden" name="auto_archive" :value="autoArchive ? '1' : '0'" />
            <!-- Hidden field for terms (Quill manages the value) -->
            <input type="hidden" name="default_terms" :value="termsContent" />

            <fieldset :disabled="!canUpdate" class="space-y-4">

                <!-- ══════════════════════════════════════════
                     SECTION 1 — TIMELINE & PAYMENT
                ══════════════════════════════════════════ -->
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <ClockIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Timeline & payment</p>
                            <p class="text-[11px] text-muted-foreground">Default windows applied to every new proposal</p>
                        </div>
                    </div>

                    <div class="grid gap-0 divide-y divide-border sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                        <!-- Validity window -->
                        <div class="space-y-2 p-5">
                            <Label for="default_validity_days" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Validity window
                            </Label>
                            <div class="relative">
                                <Input
                                    id="default_validity_days"
                                    name="default_validity_days"
                                    type="number"
                                    min="0"
                                    max="365"
                                    :default-value="proposalSettings?.default_validity_days ?? 14"
                                    class="h-10 pr-14"
                                />
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2
                                             text-xs text-muted-foreground">days</span>
                            </div>
                            <p class="text-[11px] text-muted-foreground">
                                How long clients have to accept before the proposal expires.
                            </p>
                            <InputError :message="errors.default_validity_days" />
                        </div>

                        <!-- Payment due -->
                        <div class="space-y-2 p-5">
                            <Label for="payment_due_days" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Payment due
                            </Label>
                            <div class="relative">
                                <Input
                                    id="payment_due_days"
                                    name="payment_due_days"
                                    type="number"
                                    min="0"
                                    max="365"
                                    :default-value="proposalSettings?.payment_due_days ?? 7"
                                    class="h-10 pr-14"
                                />
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2
                                             text-xs text-muted-foreground">days</span>
                            </div>
                            <p class="text-[11px] text-muted-foreground">
                                Days after acceptance before payment is due.
                            </p>
                            <InputError :message="errors.payment_due_days" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     SECTION 2 — DEPOSIT
                ══════════════════════════════════════════ -->
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <BanknoteIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Default deposit</p>
                            <p class="text-[11px] text-muted-foreground">Pre-filled when a new proposal is created</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="max-w-xs space-y-2">
                            <Label for="default_deposit_percentage" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Deposit percentage
                            </Label>
                            <div class="relative">
                                <Input
                                    id="default_deposit_percentage"
                                    name="default_deposit_percentage"
                                    type="number"
                                    min="0"
                                    max="100"
                                    :default-value="proposalSettings?.default_deposit_percentage ?? 50"
                                    class="h-10 pr-8"
                                />
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2
                                             text-xs text-muted-foreground">%</span>
                            </div>
                            <InputError :message="errors.default_deposit_percentage" />
                        </div>

                        <!-- Visual slider preview -->
                        <div class="mt-4 space-y-1.5">
                            <div class="flex justify-between text-[11px] text-muted-foreground">
                                <span>Deposit</span>
                                <span>Remaining on completion</span>
                            </div>
                            <div class="flex h-2 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="bg-primary transition-all"
                                    :style="{ width: `${proposalSettings?.default_deposit_percentage ?? 50}%` }"
                                />
                            </div>
                            <div class="flex justify-between text-[11px] font-medium text-foreground">
                                <span>{{ proposalSettings?.default_deposit_percentage ?? 50 }}%</span>
                                <span>{{ 100 - (proposalSettings?.default_deposit_percentage ?? 50) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     SECTION 3 — SENDER
                ══════════════════════════════════════════ -->
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <UserIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Sender details</p>
                            <p class="text-[11px] text-muted-foreground">Appears on the proposal cover and email</p>
                        </div>
                    </div>

                    <div class="grid gap-0 divide-y divide-border p-0 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                        <div class="space-y-2 p-5">
                            <Label for="sender_name" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Full name
                            </Label>
                            <Input
                                id="sender_name"
                                name="sender_name"
                                :default-value="proposalSettings?.sender_name ?? ''"
                                placeholder="e.g. Jane Doe"
                                class="h-10"
                            />
                            <InputError :message="errors.sender_name" />
                        </div>
                        <div class="space-y-2 p-5">
                            <Label for="sender_title" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                Job title
                            </Label>
                            <Input
                                id="sender_title"
                                name="sender_title"
                                :default-value="proposalSettings?.sender_title ?? ''"
                                placeholder="e.g. Director of Delivery"
                                class="h-10"
                            />
                            <InputError :message="errors.sender_title" />
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     SECTION 4 — BEHAVIOUR
                ══════════════════════════════════════════ -->
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <SparklesIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Behaviour</p>
                            <p class="text-[11px] text-muted-foreground">Automation rules for proposal lifecycle</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <label
                            class="flex cursor-pointer items-center justify-between rounded-lg border px-4 py-3.5 transition-colors hover:bg-muted/40"
                            :class="autoArchive ? 'border-primary bg-primary/5' : 'border-border'"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background">
                                    <ArchiveIcon class="h-4 w-4 text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-foreground">Auto-archive expired proposals</p>
                                    <p class="text-[11px] text-muted-foreground">
                                        Move proposals out of active views once they pass the validity window
                                    </p>
                                </div>
                            </div>
                            <Switch id="auto_archive" v-model="autoArchive" />
                        </label>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     SECTION 5 — DEFAULT TERMS
                ══════════════════════════════════════════ -->
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <div class="flex items-center justify-between border-b border-border bg-muted/30 px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                                <ScrollTextIcon class="h-3.5 w-3.5 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Default terms & conditions</p>
                                <p class="text-[11px] text-muted-foreground">Pre-populated in every new proposal's Terms block</p>
                            </div>
                        </div>
                        <Badge variant="outline" class="text-[11px]">Optional</Badge>
                    </div>

                    <div class="p-5">
                        <div
                            class="richtext-editor-wrap overflow-hidden rounded-lg border border-border
                                   transition-shadow focus-within:ring-2 focus-within:ring-primary
                                   focus-within:ring-offset-1"
                        >
                            <QuillEditor
                                v-model:content="termsContent"
                                theme="snow"
                                content-type="html"
                                :options="{
                                    modules: { toolbar: toolbarOptions },
                                    placeholder: 'Enter your standard terms — scope limitations, payment conditions, IP ownership, confidentiality…',
                                }"
                            />
                        </div>
                        <p class="mt-2 text-[11px] text-muted-foreground">
                            Supports rich text — headings, lists, and links.
                        </p>
                        <InputError :message="errors.default_terms" />
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     SECTION 6 — NUMBERING
                ══════════════════════════════════════════ -->
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <div class="flex items-center gap-3 border-b border-border bg-muted/30 px-5 py-3.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <HashIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Proposal numbering</p>
                            <p class="text-[11px] text-muted-foreground">Build the format of auto-generated proposal numbers</p>
                        </div>
                    </div>

                    <div class="space-y-5 p-5">

                        <!-- ① Live preview -->
                        <div class="flex items-center justify-between rounded-lg border border-dashed border-border bg-muted/30 px-4 py-3">
                            <span class="flex items-center gap-2 text-[11px] text-muted-foreground">
                                <EyeIcon class="h-3.5 w-3.5" />
                                Next proposal
                            </span>
                            <code class="rounded border border-border bg-background px-2.5 py-1 font-mono text-sm font-semibold text-foreground">
                                {{ numberPreview || '—' }}
                            </code>
                        </div>

                        <!-- ② Format builder -->
                        <div>
                            <p class="mb-2 text-xs font-medium uppercase tracking-wider text-muted-foreground">Format</p>

                            <!-- Builder zone -->
                            <div
                                class="min-h-14 rounded-lg border-2 border-dashed border-border bg-muted/20 px-3 py-3 transition-colors"
                                :class="formatTokens.length === 0 ? 'flex items-center justify-center' : ''"
                            >
                                <!-- Empty state -->
                                <p v-if="formatTokens.length === 0" class="select-none text-[11px] text-muted-foreground">
                                    Click a token below to start building the format
                                </p>

                                <!-- Token pills -->
                                <div v-else class="flex flex-wrap items-center gap-2">
                                    <div
                                        v-for="(token, i) in formatTokens"
                                        :key="`${token}-${i}`"
                                        class="group flex items-center gap-1.5 rounded-md border px-2.5 py-1.5 text-xs font-medium transition-shadow"
                                        :class="getTokenDef(token)?.pillClass"
                                    >
                                        <!-- Resolved value (live) -->
                                        <span class="font-mono font-semibold tracking-tight">{{ getTokenPreview(token) }}</span>
                                        <!-- Token label -->
                                        <span class="text-[9px] font-normal opacity-50">{{ getTokenDef(token)?.label }}</span>

                                        <!-- Controls (appear on hover) -->
                                        <span class="ml-0.5 flex items-center gap-0.5">
                                            <button
                                                v-if="i > 0"
                                                type="button"
                                                title="Move left"
                                                class="flex h-4 w-4 items-center justify-center rounded opacity-0 transition-opacity group-hover:opacity-70 hover:!opacity-100"
                                                @click="moveToken(i, -1)"
                                            >
                                                <ChevronLeftIcon class="h-3 w-3" />
                                            </button>
                                            <button
                                                v-if="i < formatTokens.length - 1"
                                                type="button"
                                                title="Move right"
                                                class="flex h-4 w-4 items-center justify-center rounded opacity-0 transition-opacity group-hover:opacity-70 hover:!opacity-100"
                                                @click="moveToken(i, 1)"
                                            >
                                                <ChevronRightIcon class="h-3 w-3" />
                                            </button>
                                            <button
                                                type="button"
                                                title="Remove"
                                                class="flex h-4 w-4 items-center justify-center rounded opacity-0 transition-opacity group-hover:opacity-70 hover:!opacity-100"
                                                @click="removeToken(i)"
                                            >
                                                <XIcon class="h-3 w-3" />
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Token palette -->
                            <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                <span class="text-[11px] text-muted-foreground">Add:</span>
                                <button
                                    v-for="def in tokenDefs"
                                    :key="def.token"
                                    type="button"
                                    :disabled="isTokenDisabled(def.token)"
                                    :title="def.description"
                                    class="flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-medium transition-all"
                                    :class="[
                                        def.pillClass,
                                        def.addClass,
                                        isTokenDisabled(def.token)
                                            ? 'cursor-not-allowed opacity-30'
                                            : 'cursor-pointer',
                                    ]"
                                    @click="addToken(def.token)"
                                >
                                    <PlusIcon class="h-3 w-3" />
                                    <span class="font-mono">{{ def.label }}</span>
                                    <span class="opacity-50">·</span>
                                    <span class="font-normal opacity-60">{{ def.description }}</span>
                                </button>
                            </div>

                            <!-- Hidden format string submitted with the form -->
                            <input type="hidden" name="numbering[format]" :value="computedFormatString" />
                            <InputError :message="errors['numbering.format']" />
                        </div>

                        <Separator />

                        <!-- ③ Configuration fields -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="numbering_prefix" class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Prefix
                                </Label>
                                <Input
                                    id="numbering_prefix"
                                    name="numbering[prefix]"
                                    :model-value="localPrefix"
                                    placeholder="PROP"
                                    class="h-9 font-mono text-sm"
                                    @update:model-value="localPrefix = String($event)"
                                />
                                <p class="text-[11px] text-muted-foreground">
                                    Resolved by the
                                    <code class="rounded border border-border bg-muted px-1 py-0.5 font-mono text-[10px] text-foreground">{PREFIX}</code>
                                    token
                                </p>
                                <InputError :message="errors['numbering.prefix']" />
                            </div>

                            <div class="space-y-2">
                                <Label for="numbering_delimiter" class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Delimiter
                                </Label>
                                <Input
                                    id="numbering_delimiter"
                                    name="numbering[delimiter]"
                                    :model-value="localDelimiter"
                                    placeholder="-"
                                    class="h-9 font-mono text-sm"
                                    @update:model-value="localDelimiter = String($event)"
                                />
                                <p class="text-[11px] text-muted-foreground">
                                    Resolved by the
                                    <code class="rounded border border-border bg-muted px-1 py-0.5 font-mono text-[10px] text-foreground">{DELIMITER}</code>
                                    token
                                </p>
                                <InputError :message="errors['numbering.delimiter']" />
                            </div>

                            <div class="space-y-2">
                                <Label for="numbering_sequence_padding" class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Sequence padding
                                </Label>
                                <Input
                                    id="numbering_sequence_padding"
                                    name="numbering[sequence_padding]"
                                    type="number"
                                    min="0"
                                    max="10"
                                    :model-value="localPadding"
                                    class="h-9 font-mono text-sm"
                                    @update:model-value="localPadding = Number($event)"
                                />
                                <p class="text-[11px] text-muted-foreground">
                                    Pads the counter with leading zeros — e.g. padding
                                    <code class="font-mono text-[10px] text-foreground">{{ localPadding }}</code>
                                    turns 1 into
                                    <code class="font-mono text-[10px] text-foreground">{{ String(1).padStart(localPadding, '0') }}</code>
                                </p>
                                <InputError :message="errors['numbering.sequence_padding']" />
                            </div>

                            <div class="space-y-2">
                                <Label for="numbering_next_sequence_number" class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Next sequence number
                                </Label>
                                <Input
                                    id="numbering_next_sequence_number"
                                    name="numbering[next_sequence_number]"
                                    type="number"
                                    min="1"
                                    :model-value="localNextSeq"
                                    class="h-9 font-mono text-sm"
                                    @update:model-value="localNextSeq = Number($event)"
                                />
                                <p class="text-[11px] text-muted-foreground">
                                    Counter assigned to the next proposal created.
                                </p>
                                <InputError :message="errors['numbering.next_sequence_number']" />
                            </div>
                        </div>
                    </div>
                </div>

            </fieldset>

            <!-- ── Save bar ──────────────────────────────────── -->
            <div class="flex items-center justify-between rounded-xl border border-border bg-card px-5 py-4">
                <p v-if="!canUpdate" class="text-sm text-muted-foreground">
                    You don't have permission to edit these settings.
                </p>
                <div v-else class="text-[11px] text-muted-foreground">
                    Changes apply to all new proposals going forward.
                </div>
                <Button
                    :disabled="processing || !canUpdate"
                    data-test="update-proposal-settings-button"
                    class="min-w-24"
                >
                    {{ processing ? 'Saving…' : 'Save changes' }}
                </Button>
            </div>

        </Form>
    </div>
</template>


