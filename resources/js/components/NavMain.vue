<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
    groupLabel?: string;
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <!--
        Group label present → render a labelled section with a
        thin top separator to visually divide sections.

        No label → render items directly (used for the Dashboard
        row at the top which needs no section heading).
    -->
    <SidebarGroup
        class="px-0 py-0"
        :class="groupLabel ? 'mt-0' : 'mt-0'"
    >
        <!-- Section label -->
        <SidebarGroupLabel
            v-if="groupLabel"
            class="h-6 px-2 mt-0.5 text-[10px] font-semibold
                   uppercase tracking-[0.08em] text-sidebar-foreground/40
                   group-data-[collapsible=icon]:opacity-0
                   group-data-[collapsible=icon]:-mt-6"
        >
            {{ groupLabel }}
        </SidebarGroupLabel>

        <SidebarMenu class="gap-0.5">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="nav-btn group/btn relative h-8 rounded-md px-2
                           text-[13px] font-medium
                           text-sidebar-foreground/70
                           hover:bg-sidebar-accent hover:text-sidebar-accent-foreground
                           data-[active=true]:bg-sidebar-accent
                           data-[active=true]:text-sidebar-accent-foreground
                           data-[active=true]:font-semibold
                           transition-colors duration-100"
                >
                    <Link :href="item.href" class="flex items-center gap-2.5">
                        <!-- Active left-edge indicator bar -->
                        <span
                            class="absolute left-0 top-1/2 h-4 w-[3px] -translate-y-1/2
                                   rounded-r-full bg-primary opacity-0 transition-opacity
                                   duration-100
                                   group-data-[active=true]/btn:opacity-100"
                        />

                        <!-- Icon — consistent size, inherits color -->
                        <component
                            :is="item.icon"
                            class="h-[15px] w-[15px] flex-shrink-0 stroke-[1.6]
                                   transition-transform duration-100
                                   group-hover/btn:scale-105"
                        />

                        <!-- Label -->
                        <span class="group-data-[collapsible=icon]:hidden">
                            {{ item.title }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>