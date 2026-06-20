<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Workspace } from '@/types/models/workspace';

const props = defineProps<{
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
        : 'EpochWeave'
);

const dynamicThemeStyles = computed(() => {
    if (!primaryColor.value) {
return {};
}

    return {
        '--tenant-primary': primaryColor.value,
        '--tenant-primary-hover': `${primaryColor.value}dd`
    };
});
</script>

<template>
    <div 
        class="min-h-screen flex flex-col bg-white font-sans antialiased"
        :style="dynamicThemeStyles"
    >
        <Head>
            <title>{{ title }} | {{ organizationName }}</title>
            <meta name="description" content="Secure document view" />
        </Head>

        <header class="sticky top-0 z-50 h-16 border-b border-slate-200/80 bg-white/80 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
                
                <div class="flex items-center gap-3">
                    <div v-if="logoUrl" class="h-8 flex items-center">
                        <img 
                            :src="logoUrl" 
                            :alt="organizationName"
                            class="h-7 w-auto max-w-[140px] object-contain object-left"
                        />
                    </div>
                    <div 
                        v-else
                        class="h-8 w-8 rounded-lg flex items-center justify-center font-bold text-sm text-white shadow-sm transition-colors"
                        :style="primaryColor ? { backgroundColor: 'var(--tenant-primary)' } : { backgroundColor: '#1e293b' }"
                    >
                        {{ organizationName.charAt(0).toUpperCase() }}
                    </div>

                    <div class="h-4 w-px bg-slate-200 hidden sm:block" />
                    <span class="text-sm font-medium text-slate-500 hidden sm:block truncate max-w-[240px]">
                        {{ organizationName }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <slot name="header-actions" />
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200/60 text-xs font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
                        Secure Live View
                    </div>
                </div>

            </div>
        </header>

        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-2 md:py-4">
            <div class="w-full h-full">
                <slot />
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>© {{ new Date().getFullYear() }} {{ organizationName }}. All rights reserved.</p>
                <div v-if="!isWhiteLabel" class="flex items-center gap-1">
                    <span>Powered by</span>
                    <a href="https://epochweave.com" class="font-medium text-slate-600 hover:text-slate-900 transition-colors">
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