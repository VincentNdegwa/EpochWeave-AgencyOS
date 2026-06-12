<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

type Props = {
    items: NavItem[];
    class?: string;
};

defineProps<Props>();

const isExternalUrl = (href: NavItem['href']): boolean => {
    return toUrl(href).startsWith('http');
};
</script>

<template>
    <SidebarGroup
        :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100"
                        as-child
                    >
                        <component
                            :is="isExternalUrl(item.href) ? 'a' : Link"
                            :href="toUrl(item.href)"
                            :target="
                                isExternalUrl(item.href) ? '_blank' : undefined
                            "
                            :rel="
                                isExternalUrl(item.href)
                                    ? 'noopener noreferrer'
                                    : undefined
                            "
                        >
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </component>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
