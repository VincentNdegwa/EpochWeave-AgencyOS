<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import WorkspaceInfo from '@/components/WorkspaceInfo.vue';
import {  switchMethod } from '@/routes/workspaces';
import type { Workspace } from '@/types/models/workspace';

type Props = {
    workspace: Workspace;
    workspaces: Workspace[];
    onSelect: (workspaceId: string) => void;
    onCreate: () => void;
};

const props = defineProps<Props>();

const otherWorkspaces = computed(() =>
    props.workspaces.filter((ws) => ws.id !== props.workspace.id),
);

const selectWorkspace = (workspaceId: string) => {
    const ws = props.workspaces.find((w) => w.id.toString() === workspaceId);

    if (ws) {
        router.post(
            switchMethod(ws.id).url,
            {},
            {
                onSuccess: () => {
                    window.location.reload();
                },
            },
        );
    }

    props.onSelect(workspaceId);
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <WorkspaceInfo :workspace="workspace" :show-description="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem
            v-for="ws in otherWorkspaces"
            :key="ws.id"
            :value="ws.id.toString()"
            @select="selectWorkspace(ws.id.toString())"
        >
            <WorkspaceInfo :workspace="ws" />
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem value="create" @select="onCreate">
            <Plus class="mr-2 h-4 w-4" />
            Create new workspace
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
