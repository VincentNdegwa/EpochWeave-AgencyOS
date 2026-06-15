<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Building2,
    Package,
    FileText,
    LayoutTemplate,
    ReceiptText,
    FolderKanban,
    CheckSquare,
    Settings2,
    Tag,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import TagController from '@/actions/App/Http/Controllers/TagController';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import accounts from '@/routes/accounts';
import products from '@/routes/products';
import invoices from '@/routes/invoices';
import proposalTemplates from '@/routes/proposal-templates';
import proposals from '@/routes/proposals';
import { index as workspaceSettings } from '@/routes/workspace-settings';
import type { NavItem } from '@/types';

const overviewNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutDashboard,
    },
];

const crmNavItems: NavItem[] = [
    {
        title: 'Accounts',
        href: accounts.index().url,
        icon: Building2,
    },
    {
        title: 'Catalog',
        href: products.index().url,
        icon: Package,
    },
];

const salesNavItems: NavItem[] = [
    {
        title: 'Proposal',
        href: proposals.index().url,
        icon: FileText,
    },
    {
        title: 'Templates',
        href: proposalTemplates.index().url,
        icon: LayoutTemplate,
    },
];

const projectNavItems: NavItem[] = [
    {
        title: 'Projects',
        href: ProjectController.index().url,
        icon: FolderKanban,
    },
    {
        title: 'Tasks',
        href: TaskController.index().url,
        icon: CheckSquare,
    },
    {
        title: 'Tags',
        href: TagController.index().url,
        icon: Tag,
    },
];

const financeNavItems: NavItem[] = [
    {
        title: 'Invoices',
        href: invoices.index().url,
        icon: ReceiptText,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Business settings',
        href: workspaceSettings(),
        icon: Settings2,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="epochweave-sidebar border-r-0">
        <SidebarHeader class="p-0 ps-2">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <div class="mx-3 border-b border-border/60" />
        </SidebarHeader>

        <SidebarContent class="py-3">
            <NavMain :items="overviewNavItems" group-label="Overview" />
            <NavMain :items="crmNavItems" group-label="CRM & Inventory" />
            <NavMain :items="salesNavItems" group-label="Sales Pipeline" />
            <NavMain :items="projectNavItems" group-label="Project Delivery" />
            <NavMain :items="financeNavItems" group-label="Finance" />
        </SidebarContent>

        <SidebarFooter class="p-0">
            <div class="mx-3 border-t border-border/60 mb-2" />
            <NavFooter :items="footerNavItems" class="px-2 pb-1" />
            <div class="px-2 pb-3">
                <NavUser />
            </div>
        </SidebarFooter>

        <SidebarRail />
    </Sidebar>
    <slot />
</template>