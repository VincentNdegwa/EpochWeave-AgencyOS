<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { CheckIcon, ChevronsUpDownIcon, PlusIcon } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { store, switchMethod } from '@/routes/workspaces';
import type { Workspace } from '@/types/models/workspace';

const page = usePage();
const workspace = page.props.workspace as Workspace | null;
const workspaces = page.props.workspaces as Workspace[];

const open = ref(false);
const value = ref(workspace?.id?.toString() || '');
const showCreateDialog = ref(false);
const newWorkspaceName = ref('');
const isCreating = ref(false);

const selectedWorkspace = computed(() =>
    workspaces.find((ws) => ws.id.toString() === value.value),
);

watch(() => page.props.workspace, (newWorkspace) => {
    if (newWorkspace) {
        value.value = newWorkspace.id?.toString() || '';
    }
});

function selectWorkspace(selectedValue: string) {
    const ws = workspaces.find((w) => w.id.toString() === selectedValue);

    if (ws) {
        router.post(switchMethod(ws.id).url, {}, {
            onSuccess: () => {
                window.location.reload();
            },
        });
    }

    open.value = false;
}

const createWorkspace = () => {
    if (!newWorkspaceName.value.trim()) {
return;
}
    
    isCreating.value = true;
    
    router.post(store().url, {
        name: newWorkspaceName.value,
        display_name: newWorkspaceName.value,
    }, {
        onSuccess: () => {
            showCreateDialog.value = false;
            newWorkspaceName.value = '';
            window.location.reload();
        },
        onFinish: () => {
            isCreating.value = false;
        },
    });
};

const openCreateDialog = () => {
    open.value = false;
    showCreateDialog.value = true;
};
</script>

<template>
    <div class="flex items-center gap-2">
        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    role="combobox"
                    :aria-expanded="open"
                    class="justify-between"
                >
                    {{ selectedWorkspace?.display_name || 'Select workspace...' }}
                    <ChevronsUpDownIcon class="opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent class="p-0">
                <Command>
                    <CommandInput class="h-9" placeholder="Search workspace..." />
                    <CommandList>
                        <CommandEmpty>No workspace found.</CommandEmpty>
                        <CommandGroup>
                            <CommandItem
                                v-for="ws in workspaces"
                                :key="ws.id"
                                :value="ws.id.toString()"
                                @select="(ev) => {
                                    selectWorkspace(ev.detail.value as string)
                                }"
                            >
                                {{ ws.display_name }}
                                <CheckIcon
                                    :class="cn(
                                        'ml-auto',
                                        value === ws.id.toString() ? 'opacity-100' : 'opacity-0',
                                    )"
                                />
                            </CommandItem>
                        </CommandGroup>
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem @select="openCreateDialog">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Create new workspace
                            </CommandItem>
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>

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
                    <Button type="submit" @click="createWorkspace" :disabled="isCreating">
                        {{ isCreating ? 'Creating...' : 'Create' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
