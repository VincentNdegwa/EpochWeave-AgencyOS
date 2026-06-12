<script setup lang="ts">
import { BuildingIcon, LayoutIcon, RulerIcon } from '@lucide/vue';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type { LogoBlockData } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<LogoBlockData>(props.blockId);

const layoutOptions = [
    { value: 'workspace_only', label: 'Workspace only' },
    { value: 'client_only', label: 'Client only' },
    { value: 'both_side_by_side', label: 'Both side by side' },
] as const;

const alignmentOptions = [
    { value: 'left', label: 'Left' },
    { value: 'center', label: 'Center' },
    { value: 'right', label: 'Right' },
] as const;

const heightOptions = [40, 60, 80, 100] as const;
</script>

<template>
    <div v-if="data" class="flex flex-col gap-0 text-sm">
        <!-- SECTION 1 — LAYOUT -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <LayoutIcon class="h-3 w-3" />
                Layout
            </p>

            <div class="space-y-1">
                <button
                    v-for="opt in layoutOptions"
                    :key="opt.value"
                    type="button"
                    class="flex w-full items-center rounded-md border px-3 py-2 text-xs font-medium transition"
                    :class="
                        data.layout === opt.value
                            ? 'border-primary bg-primary/5 text-primary'
                            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'
                    "
                    @click="updateData({ layout: opt.value })"
                >
                    {{ opt.label }}
                </button>
            </div>
        </div>

        <!-- SECTION 2 — LOGOS -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <BuildingIcon class="h-3 w-3" />
                Logo URLs
            </p>

            <div class="space-y-3">
                <div v-if="data.layout !== 'client_only'" class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground"
                        >Workspace logo URL</Label
                    >
                    <Input
                        type="url"
                        :model-value="data.workspace_logo_url ?? ''"
                        placeholder="https://example.com/logo.png"
                        class="h-8 text-xs"
                        @update:model-value="
                            (v) =>
                                updateData({
                                    workspace_logo_url: v ? String(v) : null,
                                })
                        "
                    />
                </div>

                <div
                    v-if="data.layout !== 'workspace_only'"
                    class="grid gap-1.5"
                >
                    <Label class="text-xs text-muted-foreground"
                        >Client logo URL</Label
                    >
                    <Input
                        type="url"
                        :model-value="data.client_logo_url ?? ''"
                        placeholder="https://example.com/client-logo.png"
                        class="h-8 text-xs"
                        @update:model-value="
                            (v) =>
                                updateData({
                                    client_logo_url: v ? String(v) : null,
                                })
                        "
                    />
                </div>
            </div>
        </div>

        <!-- SECTION 3 — SIZE & ALIGNMENT -->
        <div class="py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <RulerIcon class="h-3 w-3" />
                Size & alignment
            </p>

            <div class="mb-4 grid gap-1.5">
                <Label class="text-xs text-muted-foreground">Height</Label>
                <div class="grid grid-cols-4 gap-1">
                    <button
                        v-for="h in heightOptions"
                        :key="h"
                        type="button"
                        class="rounded-md border py-1.5 text-center text-xs font-medium transition"
                        :class="
                            data.height_px === h
                                ? 'border-primary bg-primary/5 text-primary'
                                : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'
                        "
                        @click="updateData({ height_px: h })"
                    >
                        {{ h }}px
                    </button>
                </div>
            </div>

            <div class="grid gap-1.5">
                <Label class="text-xs text-muted-foreground">Alignment</Label>
                <div class="grid grid-cols-3 gap-1">
                    <button
                        v-for="opt in alignmentOptions"
                        :key="opt.value"
                        type="button"
                        class="rounded-md border py-1.5 text-center text-xs font-medium transition"
                        :class="
                            data.alignment === opt.value
                                ? 'border-primary bg-primary/5 text-primary'
                                : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'
                        "
                        @click="updateData({ alignment: opt.value })"
                    >
                        {{ opt.label }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <p v-else class="py-3 text-sm text-muted-foreground">
        Block data unavailable.
    </p>
</template>
