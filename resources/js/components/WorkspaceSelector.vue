<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import WorkspaceInfo from '@/components/WorkspaceInfo.vue';
import WorkspaceMenuContent from '@/components/WorkspaceMenuContent.vue';
import { store } from '@/routes/workspaces';
import type { Workspace } from '@/types/models/workspace';

const page = usePage();
const workspace = page.props.workspace as Workspace | null;
const workspaces = page.props.workspaces as Workspace[];
const { isMobile, state } = useSidebar();

const showCreateDialog = ref(false);
const newWorkspaceName = ref('');
const isCreating = ref(false);

const createWorkspace = () => {
    if (!newWorkspaceName.value.trim()) {
        return;
    }

    isCreating.value = true;

    router.post(
        store().url,
        {
            name: newWorkspaceName.value,
            display_name: newWorkspaceName.value,
        },
        {
            onSuccess: () => {
                showCreateDialog.value = false;
                newWorkspaceName.value = '';
                window.location.reload();
            },
            onFinish: () => {
                isCreating.value = false;
            },
        },
    );
};

const openCreateDialog = () => {
    showCreateDialog.value = true;
};

const handleSelect = () => {
    // Handle selection if needed
};
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <WorkspaceInfo :workspace="workspace || workspaces[0]" />
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'bottom'
                    "
                    align="end"
                    :side-offset="4"
                >
                    <WorkspaceMenuContent
                        :workspace="workspace || workspaces[0]"
                        :workspaces="workspaces"
                        :on-select="handleSelect"
                        :on-create="openCreateDialog"
                    />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>

    <Dialog v-model:open="showCreateDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Create Workspace</DialogTitle>
                <DialogDescription>
                    Create a new workspace for your team.
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="newWorkspaceName"
                        placeholder="Workspace name"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button
                    type="submit"
                    @click="createWorkspace"
                    :disabled="isCreating"
                >
                    {{ isCreating ? 'Creating...' : 'Create' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
