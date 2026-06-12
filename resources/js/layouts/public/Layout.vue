<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import type { Workspace } from '@/types/models/workspace';

const page = usePage();
const workspace = computed(() => page.props.workspace as Workspace | undefined);

const isWhiteLabel = computed(() => workspace.value?.white_label ?? false);
const brandingName = computed(() => isWhiteLabel.value ? workspace.value?.display_name || 'Proposal Preview' : 'Proposal Preview');
const logoUrl = computed(() => workspace.value?.logo_url || null);
const primaryColor = computed(() => workspace.value?.primary_color || null);
</script>

<template>
    <div class="min-h-screen bg-background">
        <Head>
            <title>{{ brandingName }}</title>
            <meta name="description" content="Professional document preview" />
        </Head>
        
        <!-- Simple branding header -->
        <header class="h-16 border-b border-border bg-background/95 backdrop-blur">
            <div class="container mx-auto px-4 h-full flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div 
                        v-if="logoUrl" 
                        class="h-8 w-auto flex items-center justify-center"
                    >
                        <img 
                            :src="logoUrl" 
                            :alt="brandingName || 'Logo'"
                            class="h-8 w-auto max-w-[120px] object-contain"
                        />
                    </div>
                    <div 
                        v-else
                        class="h-8 w-8 rounded flex items-center justify-center"
                        :style="primaryColor ? { backgroundColor: primaryColor } : {}"
                    >
                        <span class="text-white font-bold text-sm">
                            {{ (brandingName || 'P').charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <span class="font-semibold text-foreground">{{ brandingName }}</span>
                </div>
              
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>
