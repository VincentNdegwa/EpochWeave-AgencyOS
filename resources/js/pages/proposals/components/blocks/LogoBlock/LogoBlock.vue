<script setup lang="ts">
import { BuildingIcon } from '@lucide/vue';
import { computed } from 'vue';
import type { BaseBlock, LogoBlockData } from '@/types/proposal-builder';

const props = defineProps<{
    data: LogoBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const alignClass = computed(
    () =>
        ({
            left: 'justify-start',
            center: 'justify-center',
            right: 'justify-end',
        })[props.data.alignment] ?? 'justify-center',
);

const heightStyle = computed(() => ({ height: `${props.data.height_px}px` }));
</script>

<template>
    <section class="px-8 py-4">
        <div class="flex items-center gap-8" :class="alignClass">
            <!-- Workspace logo -->
            <template
                v-if="
                    data.layout === 'workspace_only' ||
                    data.layout === 'both_side_by_side'
                "
            >
                <img
                    v-if="data.workspace_logo_url"
                    :src="data.workspace_logo_url"
                    alt="Workspace logo"
                    class="w-auto object-contain"
                    :style="heightStyle"
                />
                <div
                    v-else
                    class="flex items-center gap-2 rounded-md border border-dashed border-border px-3"
                    :style="heightStyle"
                >
                    <BuildingIcon class="h-4 w-4 text-muted-foreground" />
                    <span class="text-xs text-muted-foreground"
                        >Workspace logo</span
                    >
                </div>
            </template>

            <!-- Separator for both -->
            <div
                v-if="data.layout === 'both_side_by_side'"
                class="h-6 w-px bg-border"
            />

            <!-- Client logo -->
            <template
                v-if="
                    data.layout === 'client_only' ||
                    data.layout === 'both_side_by_side'
                "
            >
                <img
                    v-if="data.client_logo_url"
                    :src="data.client_logo_url"
                    alt="Client logo"
                    class="w-auto object-contain"
                    :style="heightStyle"
                />
                <div
                    v-else
                    class="flex items-center gap-2 rounded-md border border-dashed border-border px-3"
                    :style="heightStyle"
                >
                    <BuildingIcon class="h-4 w-4 text-muted-foreground" />
                    <span class="text-xs text-muted-foreground"
                        >Client logo</span
                    >
                </div>
            </template>
        </div>
    </section>
</template>
