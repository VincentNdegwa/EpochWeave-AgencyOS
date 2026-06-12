<script setup lang="ts">
import { SeparatorHorizontalIcon, PaletteIcon } from '@lucide/vue';
import { ColorPresets } from '@/components/ui/color-presets';
import { Label } from '@/components/ui/label';
import { useBlockSettings } from '@/composables/useBlockSettings';
import type { DividerBlockData } from '@/types/proposal-builder';

const props = defineProps<{ blockId: string }>();

const { data, updateData } = useBlockSettings<DividerBlockData>(props.blockId);

const styleOptions = [
    { value: 'solid', label: 'Solid' },
    { value: 'dashed', label: 'Dashed' },
    { value: 'dotted', label: 'Dotted' },
    { value: 'none', label: 'None' },
] as const;

const thicknessOptions = [
    { value: 1, label: '1px' },
    { value: 2, label: '2px' },
    { value: 4, label: '4px' },
] as const;
</script>

<template>
    <div v-if="data" class="flex flex-col gap-0 text-sm">
        <!-- SECTION 1 — STYLE -->
        <div class="border-b border-border py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <SeparatorHorizontalIcon class="h-3 w-3" />
                Style
            </p>

            <div class="grid gap-3">
                <div class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground"
                        >Line style</Label
                    >
                    <div class="grid grid-cols-4 gap-1">
                        <button
                            v-for="opt in styleOptions"
                            :key="opt.value"
                            type="button"
                            class="rounded-md border py-1.5 text-center text-xs font-medium transition"
                            :class="
                                data.style === opt.value
                                    ? 'border-primary bg-primary/5 text-primary'
                                    : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'
                            "
                            @click="updateData({ style: opt.value })"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <!-- Preview -->
                <div
                    class="rounded-md border border-border bg-muted/30 px-4 py-3"
                >
                    <hr
                        :style="{
                            borderTopStyle:
                                data.style === 'none' ? 'none' : data.style,
                            borderTopWidth: `${data.thickness_px}px`,
                            borderTopColor: data.color,
                        }"
                        class="border-0"
                    />
                    <p
                        v-if="data.style === 'none'"
                        class="text-center text-[11px] text-muted-foreground"
                    >
                        No line
                    </p>
                </div>

                <div v-if="data.style !== 'none'" class="grid gap-1.5">
                    <Label class="text-xs text-muted-foreground"
                        >Thickness</Label
                    >
                    <div class="grid grid-cols-3 gap-1">
                        <button
                            v-for="opt in thicknessOptions"
                            :key="opt.value"
                            type="button"
                            class="rounded-md border py-1.5 text-center text-xs font-medium transition"
                            :class="
                                data.thickness_px === opt.value
                                    ? 'border-primary bg-primary/5 text-primary'
                                    : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'
                            "
                            @click="updateData({ thickness_px: opt.value })"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2 — COLOUR -->
        <div v-if="data.style !== 'none'" class="py-3">
            <p
                class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
            >
                <PaletteIcon class="h-3 w-3" />
                Colour
            </p>
            <ColorPresets
                :model-value="data.color"
                placeholder="#e5e7eb"
                @update:model-value="(v) => updateData({ color: v })"
            />
        </div>
    </div>
    <p v-else class="py-3 text-sm text-muted-foreground">
        Block data unavailable.
    </p>
</template>
