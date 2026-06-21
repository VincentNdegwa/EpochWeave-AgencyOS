<script setup lang="ts">
import {
    HashIcon,
    EyeIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XIcon,
    PlusIcon,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    initialValues?: {
        format?: string;
        prefix?: string;
        delimiter?: string;
        sequence_padding?: number;
        next_sequence_number?: number;
    };
    errors?: Record<string, string>;
    namePrefix?: string;
    previewLabel?: string;
    defaultPrefix?: string;
}

const props = withDefaults(defineProps<Props>(), {
    initialValues: () => ({}),
    errors: () => ({}),
    namePrefix: 'numbering',
    previewLabel: 'Next number',
    defaultPrefix: 'PREFIX',
});

const localPrefix = ref(props.initialValues.prefix ?? props.defaultPrefix);
const localDelimiter = ref(props.initialValues.delimiter ?? '-');
const localPadding = ref(props.initialValues.sequence_padding ?? 4);
const localNextSeq = ref(props.initialValues.next_sequence_number ?? 1);

function parseFormatString(format: string): string[] {
    return format.match(/\{[A-Z]+\}/g) ?? [];
}

const formatTokens = ref<string[]>(
    parseFormatString(
        props.initialValues.format ?? '{PREFIX}{DELIMITER}{SEQUENCE}',
    ),
);

const computedFormatString = computed(() => formatTokens.value.join(''));

const numberPreview = computed(() => {
    const padded = String(localNextSeq.value).padStart(localPadding.value, '0');

    return computedFormatString.value
        .replace('{PREFIX}', localPrefix.value || props.defaultPrefix)
        .replace(/{DELIMITER}/g, localDelimiter.value || '-')
        .replace('{YEAR}', new Date().getFullYear().toString())
        .replace('{SEQUENCE}', padded);
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
    if (token === '{PREFIX}') {
        return localPrefix.value || 'PREFIX';
    }

    if (token === '{DELIMITER}') {
        return localDelimiter.value || '—';
    }

    if (token === '{YEAR}') {
        return new Date().getFullYear().toString();
    }

    if (token === '{SEQUENCE}') {
        return String(localNextSeq.value).padStart(localPadding.value, '0');
    }

    return token;
}

function isTokenDisabled(token: string): boolean {
    const def = getTokenDef(token);

    if (!def?.unique) {
        return false;
    }

    return formatTokens.value.includes(token);
}

function addToken(token: string) {
    if (isTokenDisabled(token)) {
        return;
    }

    formatTokens.value = [...formatTokens.value, token];
}

function removeToken(index: number) {
    formatTokens.value = formatTokens.value.filter((_, i) => i !== index);
}

function moveToken(index: number, direction: -1 | 1) {
    const arr = [...formatTokens.value];
    const newIndex = index + direction;

    if (newIndex < 0 || newIndex >= arr.length) {
        return;
    }

    [arr[index], arr[newIndex]] = [arr[newIndex], arr[index]];
    formatTokens.value = arr;
}

function fieldError(key: string): string | undefined {
    return props.errors[`${props.namePrefix}.${key}`];
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <div
                class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10"
            >
                <HashIcon class="h-3.5 w-3.5 text-primary" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-foreground">Numbering</h3>
                <p class="text-xs text-muted-foreground">
                    Build the format of auto-generated numbers
                </p>
            </div>
        </div>

        <div class="space-y-4">
            <div
                class="flex items-center justify-between rounded-lg border border-dashed border-border bg-muted/30 px-4 py-3"
            >
                <span
                    class="flex items-center gap-2 text-xs text-muted-foreground"
                >
                    <EyeIcon class="h-3.5 w-3.5" />
                    {{ previewLabel }}
                </span>
                <code
                    class="rounded border border-border bg-background px-2.5 py-1 font-mono text-sm font-semibold text-foreground"
                >
                    {{ numberPreview || '—' }}
                </code>
            </div>

            <div class="grid gap-2">
                <Label>Format</Label>
                <div
                    class="min-h-14 rounded-lg border-2 border-dashed border-border bg-muted/20 px-3 py-3 transition-colors"
                    :class="
                        formatTokens.length === 0
                            ? 'flex items-center justify-center'
                            : ''
                    "
                >
                    <p
                        v-if="formatTokens.length === 0"
                        class="text-xs text-muted-foreground select-none"
                    >
                        Click a token below to start building the format
                    </p>
                    <div v-else class="flex flex-wrap items-center gap-2">
                        <div
                            v-for="(token, i) in formatTokens"
                            :key="`${token}-${i}`"
                            class="group flex items-center gap-1.5 rounded-md border px-2.5 py-1.5 text-xs font-medium transition-shadow"
                            :class="getTokenDef(token)?.pillClass"
                        >
                            <span
                                class="font-mono font-semibold tracking-tight"
                                >{{ getTokenPreview(token) }}</span
                            >
                            <span class="text-[9px] font-normal opacity-50">{{
                                getTokenDef(token)?.label
                            }}</span>
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
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-muted-foreground">Add:</span>
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
                        <span class="font-normal opacity-60">{{
                            def.description
                        }}</span>
                    </button>
                </div>
                <input
                    type="hidden"
                    :name="`${namePrefix}[format]`"
                    :value="computedFormatString"
                />
                <InputError :message="fieldError('format')" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label :for="`${namePrefix}_prefix`">Prefix</Label>
                    <Input
                        :id="`${namePrefix}_prefix`"
                        :name="`${namePrefix}[prefix]`"
                        v-model="localPrefix"
                        :placeholder="defaultPrefix"
                        class="font-mono text-sm"
                    />
                    <InputError :message="fieldError('prefix')" />
                </div>

                <div class="grid gap-2">
                    <Label :for="`${namePrefix}_delimiter`">Delimiter</Label>
                    <Input
                        :id="`${namePrefix}_delimiter`"
                        :name="`${namePrefix}[delimiter]`"
                        v-model="localDelimiter"
                        placeholder="-"
                        class="font-mono text-sm"
                    />
                    <InputError :message="fieldError('delimiter')" />
                </div>

                <div class="grid gap-2">
                    <Label :for="`${namePrefix}_sequence_padding`"
                        >Sequence padding</Label
                    >
                    <Input
                        :id="`${namePrefix}_sequence_padding`"
                        :name="`${namePrefix}[sequence_padding]`"
                        type="number"
                        min="0"
                        max="10"
                        v-model="localPadding"
                        class="font-mono text-sm"
                    />
                    <InputError :message="fieldError('sequence_padding')" />
                </div>

                <div class="grid gap-2">
                    <Label :for="`${namePrefix}_next_sequence_number`"
                        >Next sequence number</Label
                    >
                    <Input
                        :id="`${namePrefix}_next_sequence_number`"
                        :name="`${namePrefix}[next_sequence_number]`"
                        type="number"
                        min="1"
                        v-model="localNextSeq"
                        class="font-mono text-sm"
                    />
                    <InputError :message="fieldError('next_sequence_number')" />
                </div>
            </div>
        </div>
    </div>
</template>
