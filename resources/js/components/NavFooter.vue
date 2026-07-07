<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

type Props = {
    items: NavItem[];
    class?: string;
};

defineProps<Props>();

const { isCurrentUrl } = useCurrentUrl();

const isExternalUrl = (href: NavItem['href']): boolean =>
    toUrl(href).startsWith('http');
</script>

<template>
    <SidebarGroup
        :class="`px-0 py-0 group-data-[collapsible=icon]:p-0 ${$props.class ?? ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu class="gap-0.5">
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentUrl(toUrl(item.href))"
                        :tooltip="item.title"
                        class="group/footbtn relative h-8 rounded-md px-2
                               text-[13px] font-medium
                               text-sidebar-foreground/60
                               hover:bg-sidebar-accent hover:text-sidebar-accent-foreground
                               data-[active=true]:bg-sidebar-accent
                               data-[active=true]:text-sidebar-accent-foreground
                               transition-colors duration-100"
                    >
                        <component
                            :is="isExternalUrl(item.href) ? 'a' : Link"
                            :href="toUrl(item.href)"
                            :target="isExternalUrl(item.href) ? '_blank' : undefined"
                            :rel="isExternalUrl(item.href) ? 'noopener noreferrer' : undefined"
                            class="flex items-center gap-2.5"
                        >
                            <component
                                :is="item.icon"
                                class="h-[15px] w-[15px] flex-shrink-0 stroke-[1.6]"
                            />
                            <span class="group-data-[collapsible=icon]:hidden">
                                {{ item.title }}
                            </span>
                        </component>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>