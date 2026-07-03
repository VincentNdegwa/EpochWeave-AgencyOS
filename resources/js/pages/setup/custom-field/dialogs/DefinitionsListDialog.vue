<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { ref, watch } from 'vue';
import CustomFieldController from '@/actions/App/Http/Controllers/CustomFieldController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type {
    CustomFieldDefinition,
    CustomFieldGroup,
} from '@/types/models/custom_field';

interface Props {
    open: boolean;
    group?: CustomFieldGroup | null;
}

const props = withDefaults(defineProps<Props>(), {
    group: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    edit: [definition: CustomFieldDefinition];
}>();

const localDefinitions = ref<CustomFieldDefinition[]>([]);

watch(
    () => props.group,
    (group) => {
        localDefinitions.value = group ? [...group.definitions] : [];
    },
);

async function handleDelete(definition: CustomFieldDefinition) {
    const { confirm } = await import('@/composables/useConfirmation');

    if (
        await confirm({
            title: 'Delete Field',
            description:
                'Are you sure you want to delete this field definition?',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            variant: 'destructive',
        })
    ) {
        router.delete(
            CustomFieldController.destroyDefinition(definition.id).url,
        );
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle> {{ group?.name }} Fields </DialogTitle>
                <DialogDescription>
                    Manage the fields in this group.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-2 py-4">
                <div
                    v-for="definition in localDefinitions"
                    :key="definition.id"
                    class="flex items-center justify-between rounded-md border border-border bg-muted/30 px-3 py-2"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">{{
                            definition.label
                        }}</span>
                        <span
                            class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground uppercase"
                        >
                            {{ definition.field_type }}
                        </span>
                        <span
                            v-if="definition.is_required"
                            class="rounded bg-destructive/10 px-1.5 py-0.5 text-[10px] font-medium text-destructive"
                        >
                            Required
                        </span>
                    </div>
                    <div class="flex items-center gap-1">
                        <Button
                            size="sm"
                            variant="ghost"
                            @click="emit('edit', definition)"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            size="sm"
                            variant="ghost"
                            class="text-destructive"
                            @click="handleDelete(definition)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>
                <div
                    v-if="localDefinitions.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No fields in this group.
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
