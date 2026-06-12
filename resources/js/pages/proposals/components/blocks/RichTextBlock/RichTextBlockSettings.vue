<script setup lang="ts">
import { TypeIcon, PaletteIcon, ToggleLeftIcon } from '@lucide/vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { TextColorPicker } from '@/components/ui/text-color-picker';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type { RichTextBlockData } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<RichTextBlockData>(props.blockId);
</script>

<template>
    <div v-if="data" class="flex flex-col gap-0 text-sm">
        <!-- ══════════════════════════════════════════════
         SECTION 1 — TITLE
    ══════════════════════════════════════════════ -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <TypeIcon class="h-3 w-3" />
                Section title
            </p>

            <!-- Show title toggle -->
            <label
                class="mb-3 flex cursor-pointer items-center justify-between rounded-md border border-border px-3 py-2.5 transition-colors hover:bg-muted/50"
            >
                <div>
                    <p class="text-xs font-medium text-foreground">
                        Show title
                    </p>
                    <p class="text-[11px] text-muted-foreground">
                        Display a heading above the text
                    </p>
                </div>
                <Switch
                    :model-value="data.show_title ?? true"
                    @update:model-value="
                        (v: boolean) => updateData({ show_title: v })
                    "
                />
            </label>

            <!-- Title input — only when show_title is on -->
            <template v-if="data.show_title ?? true">
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground"
                        >Title text</Label
                    >
                    <Input
                        :model-value="data.title ?? ''"
                        placeholder="e.g. Our Approach"
                        class="h-8 text-sm"
                        @update:model-value="
                            (v) => updateData({ title: v ? String(v) : null })
                        "
                    />
                </div>
            </template>
        </div>

        <!-- ══════════════════════════════════════════════
         SECTION 2 — COLOURS
    ══════════════════════════════════════════════ -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <PaletteIcon class="h-3 w-3" />
                Colours
            </p>

            <!-- Title colour -->
            <div v-if="data.show_title ?? true" class="mb-4 grid gap-2">
                <Label class="text-xs text-muted-foreground"
                    >Title colour</Label
                >
                <TextColorPicker
                    :model-value="data.title_color ?? '#111827'"
                    light-color="#FFFFFF"
                    dark-color="#111827"
                    @update:model-value="(v) => updateData({ title_color: v })"
                />
            </div>

            <!-- Content colour -->
            <div class="mb-4 grid gap-2">
                <Label class="text-xs text-muted-foreground"
                    >Content colour</Label
                >
                <TextColorPicker
                    :model-value="data.content_color ?? '#374151'"
                    light-color="#FFFFFF"
                    dark-color="#111827"
                    @update:model-value="
                        (v) => updateData({ content_color: v })
                    "
                />
            </div>

            <!-- Background now controlled via block meta panel -->
        </div>

        <!-- ══════════════════════════════════════════════
         SECTION 3 — DISPLAY OPTIONS
    ══════════════════════════════════════════════ -->
        <div class="py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <ToggleLeftIcon class="h-3 w-3" />
                Display
            </p>

            <div
                class="space-y-0 divide-y divide-border overflow-hidden rounded-lg border border-border"
            >
                <!-- Full width toggle -->
                <label
                    class="flex cursor-pointer items-center justify-between px-3 py-2.5 transition-colors hover:bg-muted/50"
                >
                    <div>
                        <p class="text-xs font-medium text-foreground">
                            Full width text
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            Remove max-width constraint on content
                        </p>
                    </div>
                    <Switch
                        :model-value="data.full_width ?? false"
                        @update:model-value="
                            (v: boolean) => updateData({ full_width: v })
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
