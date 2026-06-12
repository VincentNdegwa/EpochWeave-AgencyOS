<script setup lang="ts">
import { PenLineIcon, ToggleLeftIcon } from '@lucide/vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type { SignatureBlockData } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<SignatureBlockData>(
    props.blockId,
);
</script>

<template>
    <div v-if="data" class="flex flex-col gap-0 text-sm">
        <!-- SECTION 1 — CONTENT -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <PenLineIcon class="h-3 w-3" />
                Content
            </p>
            <div class="space-y-3">
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground">Title</Label>
                    <Input
                        :model-value="data.title ?? ''"
                        placeholder="Authorization & Sign-Off"
                        class="h-8 text-sm"
                        @update:model-value="
                            (v) => updateData({ title: v ? String(v) : null })
                        "
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground"
                        >Description</Label
                    >
                    <Input
                        :model-value="data.description ?? ''"
                        placeholder="Please sign below to accept this proposal"
                        class="h-8 text-xs"
                        @update:model-value="
                            (v) =>
                                updateData({
                                    description: v ? String(v) : null,
                                })
                        "
                    />
                </div>
            </div>
        </div>

        <!-- SECTION 2 — REQUIRED FIELDS -->
        <div class="py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <ToggleLeftIcon class="h-3 w-3" />
                Required fields
            </p>

            <div
                class="space-y-0 divide-y divide-border overflow-hidden rounded-lg border border-border"
            >
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Require full name
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            Client must type their name
                        </p>
                    </div>
                    <Switch
                        :model-value="data.require_name"
                        @update:model-value="
                            (v: boolean) => updateData({ require_name: v })
                        "
                    />
                </label>
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Require date
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            Client confirms the signing date
                        </p>
                    </div>
                    <Switch
                        :model-value="data.require_date"
                        @update:model-value="
                            (v: boolean) => updateData({ require_date: v })
                        "
                    />
                </label>
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Require drawn signature
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            Canvas signature pad
                        </p>
                    </div>
                    <Switch
                        :model-value="data.require_signature"
                        @update:model-value="
                            (v: boolean) => updateData({ require_signature: v })
                        "
                    />
                </label>
            </div>
        </div>
    </div>
    <p v-else class="py-3 text-sm text-muted-foreground">
        Block data unavailable.
    </p>
</template>
