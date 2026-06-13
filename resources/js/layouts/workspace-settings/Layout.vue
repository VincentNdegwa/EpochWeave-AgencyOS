<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell, FileText, Settings, Sparkles } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { general, proposals } from '@/routes/workspace-settings';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'General',
        href: general(),
        icon: Settings,
    },
    {
        title: 'Proposals',
        href: proposals(),
        icon: FileText,
    },
    {
        title: 'Notifications',
        href: '/workspace/settings/notifications',
        icon: Bell,
    },
    {
        title: 'Automation',
        href: '/workspace/settings/automation',
        icon: Sparkles,
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Business settings"
            description="Manage your current workspace settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Business settings"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isCurrentOrParentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href" class="flex items-center gap-2">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
