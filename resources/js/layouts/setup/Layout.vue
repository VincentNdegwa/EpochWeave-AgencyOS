<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Factory, FileCheck, Folder, List, ListTodo, Receipt, Target, Users } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Industries',
        href: '/setup/industries',
        icon: Factory,
    },
    {
        title: 'Lead Sources',
        href: '/setup/lead-sources',
        icon: Target,
    },
    {
        title: 'Company Sizes',
        href: '/setup/company-sizes',
        icon: Users,
    },
    {
        title: 'Custom Fields',
        href: '/setup/custom-fields',
        icon: List,
    },
    {
        title: 'Proposal Statuses',
        href: '/setup/proposal-status',
        icon: FileCheck,
    },
    {
        title: 'Project Statuses',
        href: '/setup/project-status',
        icon: Folder,
    },
    {
        title: 'Task Statuses',
        href: '/setup/task-status',
        icon: ListTodo,
    },
    {
        title: 'Invoice Statuses',
        href: '/setup/invoice-status',
        icon: Receipt,
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Setup"
            description="Configure workspace options and lookups"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Setup"
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
