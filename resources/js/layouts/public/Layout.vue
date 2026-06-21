<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Workspace } from '@/types/models/workspace';

defineProps<{
    title: string;
}>();

const page = usePage();
const workspace = computed(() => page.props.workspace as Workspace | undefined);

const isWhiteLabel = computed(() => workspace.value?.white_label ?? false);
const logoUrl = computed(() => workspace.value?.logo_url || null);
const primaryColor = computed(() => workspace.value?.primary_color || null);

const organizationName = computed(() =>
    isWhiteLabel.value && workspace.value?.display_name
        ? workspace.value.display_name
        : 'EpochWeave',
);

const dynamicThemeStyles = computed(() => {
    if (!primaryColor.value) {
        return {};
    }

    return {
        '--tenant-primary': primaryColor.value,
        '--tenant-primary-hover': `${primaryColor.value}dd`,
    };
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-white font-sans antialiased"
        :style="dynamicThemeStyles"
    >
        <Head>
            <title>{{ title }} | {{ organizationName }}</title>
            <meta name="description" content="Secure document view" />
        </Head>

        <header
            class="sticky top-0 z-50 h-16 border-b border-slate-200/80 bg-white/80 backdrop-blur-md"
        >
            <div
                class="mx-auto flex h-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <div v-if="logoUrl" class="flex h-8 items-center">
                        <img
                            :src="logoUrl"
                            :alt="organizationName"
                            class="h-7 w-auto max-w-[140px] object-contain object-left"
                        />
                    </div>
                    <div
                        v-else
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold text-white shadow-sm transition-colors"
                        :style="
                            primaryColor
                                ? { backgroundColor: 'var(--tenant-primary)' }
                                : { backgroundColor: '#1e293b' }
                        "
                    >
                        {{ organizationName.charAt(0).toUpperCase() }}
                    </div>

                    <div class="hidden h-4 w-px bg-slate-200 sm:block" />
                    <span
                        class="hidden max-w-[240px] truncate text-sm font-medium text-slate-500 sm:block"
                    >
                        {{ organizationName }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <slot name="header-actions" />
                    <div
                        class="flex items-center gap-1.5 rounded-full border border-slate-200/60 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                    >
                        <span
                            class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"
                        />
                        Secure Live View
                    </div>
                </div>
            </div>
        </header>

        <main
            class="mx-auto w-full max-w-7xl flex-1 px-4 py-2 sm:px-6 md:py-4 lg:px-8"
        >
            <div class="h-full w-full">
                <slot />
            </div>
        </main>

        <footer class="mt-auto border-t border-slate-200 bg-white py-6">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 text-xs text-slate-400 sm:flex-row sm:px-6 lg:px-8"
            >
                <p>
                    © {{ new Date().getFullYear() }} {{ organizationName }}. All
                    rights reserved.
                </p>
                <div v-if="!isWhiteLabel" class="flex items-center gap-1">
                    <span>Powered by</span>
                    <a
                        href="https://epochweave.com"
                        class="font-medium text-slate-600 transition-colors hover:text-slate-900"
                    >
                        EpochWeave
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
:deep(.btn-primary) {
    background-color: var(--tenant-primary, #1e293b);
    color: #ffffff;
}
:deep(.btn-primary:hover) {
    background-color: var(--tenant-primary-hover, #0f172a);
}
:deep(.text-accent) {
    color: var(--tenant-primary, #1e293b);
}
</style>
