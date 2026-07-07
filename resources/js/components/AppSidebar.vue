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
    Wrench,
    BarChart3,
} from '@lucide/vue';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import TaskController from '@/actions/App/Http/Controllers/TaskController';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
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
import invoices from '@/routes/invoices';
import products from '@/routes/products';
import proposalTemplates from '@/routes/proposal-templates';
import proposals from '@/routes/proposals';
import reports from '@/routes/reports';
import { index as workspaceSettings } from '@/routes/workspace-settings';
import type { NavItem } from '@/types';

const overviewNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutDashboard },
];

const crmNavItems: NavItem[] = [
    { title: 'Accounts', href: accounts.index().url, icon: Building2 },
    { title: 'Catalog',  href: products.index().url, icon: Package    },
];

const salesNavItems: NavItem[] = [
    { title: 'Proposals',  href: proposals.index().url,         icon: FileText       },
    { title: 'Templates',  href: proposalTemplates.index().url, icon: LayoutTemplate },
];

const projectNavItems: NavItem[] = [
    { title: 'Projects', href: ProjectController.index().url, icon: FolderKanban },
    { title: 'Tasks',    href: TaskController.index().url,    icon: CheckSquare  },
];

const financeNavItems: NavItem[] = [
    { title: 'Invoices', href: invoices.index().url, icon: ReceiptText },
];

const reportsNavItems: NavItem[] = [
    { title: 'Reports', href: reports.index().url, icon: BarChart3 },
];

const footerNavItems: NavItem[] = [
    { title: 'Business Setup',     href: '/setup/industries', icon: Wrench   },
    { title: 'Business settings',  href: workspaceSettings(), icon: Settings2 },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">

        <!-- ── Logo / brand header ──────────────────────── -->
        <SidebarHeader class="p-0">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="h-auto px-3 py-3.5 hover:bg-transparent
                               focus-visible:ring-0 active:bg-transparent"
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <!-- Hairline separator below brand -->
            <div class="mx-3 border-b border-sidebar-border/50" />
        </SidebarHeader>

        <!-- ── Navigation ───────────────────────────────── -->
        <SidebarContent class="px-2 py-3 gap-0">
            <NavMain :items="overviewNavItems" />

            <NavMain
                :items="crmNavItems"
                group-label="CRM & Inventory"
            />
            <NavMain
                :items="salesNavItems"
                group-label="Sales"
            />
            <NavMain
                :items="projectNavItems"
                group-label="Delivery"
            />
            <NavMain
                :items="financeNavItems"
                group-label="Finance"
            />
            <NavMain
                :items="reportsNavItems"
                group-label="Reports"
            />
        </SidebarContent>

        <!-- ── Footer ────────────────────────────────────── -->
        <SidebarFooter class="p-0">
            <div class="mx-3 border-t border-sidebar-border/50" />
            <NavFooter :items="footerNavItems" class="px-2 pt-1 pb-1" />
            <div class="px-2 pb-3">
                <NavUser />
            </div>
        </SidebarFooter>

        <SidebarRail />
    </Sidebar>
    <slot />
</template>