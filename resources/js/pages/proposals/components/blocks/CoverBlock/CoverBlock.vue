<script setup lang="ts">
import { computed } from 'vue';
import { useWorkspaceStore } from '@/stores/workspace';
import type { BaseBlock, CoverBlockData } from '@/types/proposal-builder';

const props = defineProps<{
    data: CoverBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const emit = defineEmits<{
    'update:data': [data: Partial<CoverBlockData>];
}>();

const workspaceStore = useWorkspaceStore();

const updateData = (changes: Partial<CoverBlockData>) => {
    emit('update:data', changes);
};

const containerStyle = computed(() => {
    if (props.data.background_type === 'image') {
        return {
            backgroundImage: `url(${props.data.background_value})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            color: props.data.text_color,
        } as Record<string, string>;
    }

    return {
        backgroundColor: props.data.background_value || '#0F172A',
        color: props.data.text_color || '#FFFFFF',
    } as Record<string, string>;
});

const accentStyle = computed(() => ({
    backgroundColor: props.data.text_color || '#FFFFFF',
    opacity: '0.4',
}));

const hasImage = computed(
    () =>
        props.data.background_type === 'image' && !!props.data.background_value,
);

const today = computed(() =>
    new Date().toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }),
);

const shortId = computed(() => props.block.id.slice(-6).toUpperCase());
</script>

<template>
    <section
        class="group/cover relative flex min-h-[420px] w-full flex-col overflow-hidden px-8 select-none"
        :style="containerStyle"
    >
        <div
            v-if="hasImage"
            class="pointer-events-none absolute inset-0 bg-black/50"
        />

        <div
            class="pointer-events-none absolute -top-16 -right-16 h-64 w-64 rounded-full opacity-10"
            :style="{ background: data.text_color || '#FFFFFF' }"
        />
        <div
            class="pointer-events-none absolute -top-8 -right-8 h-40 w-40 rounded-full opacity-[0.07]"
            :style="{ background: data.text_color || '#FFFFFF' }"
        />

        <header class="relative z-10 flex items-start justify-between pt-10">
            <div v-if="data.show_logo" class="flex items-center gap-2.5">
                <img
                    v-if="workspaceStore.logoUrl"
                    :src="workspaceStore.logoUrl"
                    alt="Workspace logo"
                    class="h-8 w-auto object-contain"
                />
                <span
                    v-else
                    class="text-sm font-semibold tracking-wide"
                    style="opacity: 0.85"
                >
                    {{ workspaceStore.name ?? 'Your Company' }}
                </span>
            </div>
            <div v-else />

            <div
                class="space-y-0.5 text-right font-mono text-[11px] tracking-wider"
                style="opacity: 0.55"
            >
                <p v-if="data.show_proposal_number" class="uppercase">
                    Proposal #{{ shortId }}
                </p>
                <p v-if="data.show_date">{{ today }}</p>
            </div>
        </header>

        <div class="relative z-10 mt-auto pb-14">
            <h1
                :contenteditable="!isLocked"
                :class="[
                    'max-w-2xl text-5xl leading-[1.1] font-bold tracking-tight outline-none',
                    !isLocked
                        ? 'cursor-text rounded focus:ring-2 focus:ring-white/30 focus:ring-offset-0'
                        : '',
                ]"
                :suppressContentEditableWarning="true"
                @blur="
                    (e) =>
                        updateData({
                            heading:
                                (e.target as HTMLElement).innerText.trim() ||
                                'Proposal Title',
                        })
                "
                @keydown.enter.prevent="($event.target as HTMLElement).blur()"
            >
                {{ data.heading || 'Proposal Title' }}
            </h1>

            <p
                :contenteditable="!isLocked"
                :class="[
                    'mt-4 max-w-xl text-lg leading-relaxed outline-none',
                    !isLocked
                        ? 'cursor-text rounded focus:ring-2 focus:ring-white/30'
                        : '',
                    !data.subheading && !isLocked ? 'opacity-40' : 'opacity-75',
                ]"
                :data-placeholder="!isLocked ? 'Add a subtitle…' : ''"
                :suppressContentEditableWarning="true"
                @blur="
                    (e) => {
                        const v = (e.target as HTMLElement).innerText.trim();
                        updateData({ subheading: v || null });
                    }
                "
            >
                {{ data.subheading || (!isLocked ? '' : '') }}
            </p>

            <div class="mt-8 h-1 w-14 rounded-full" :style="accentStyle" />
        </div>

        <div
            v-if="!isLocked"
            class="pointer-events-none absolute right-4 bottom-14 rounded-md bg-black/30 px-2 py-1 text-[10px] text-white/60 opacity-0 transition-opacity group-hover/cover:opacity-100"
        >
            Click text to edit
        </div>
    </section>
</template>

<style scoped>
[contenteditable]:empty::before {
    content: attr(data-placeholder);
    pointer-events: none;
    display: block;
}
</style>
