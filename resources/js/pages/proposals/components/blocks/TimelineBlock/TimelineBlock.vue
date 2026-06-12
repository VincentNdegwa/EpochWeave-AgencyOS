<script setup lang="ts">
import { PlusIcon, Trash2Icon, XIcon } from '@lucide/vue';
import { nanoid } from 'nanoid';
import BlockEmptyPlaceholder from '@/pages/proposals/components/blocks/shared/BlockEmptyPlaceholder.vue';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type {
    BaseBlock,
    TimelineBlockData,
    TimelineMilestone,
} from '@/types/proposal-builder';

const props = defineProps<{
    data: TimelineBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<TimelineBlockData>) => {
    store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const updateMilestone = (id: string, changes: Partial<TimelineMilestone>) => {
    updateData({
        milestones: props.data.milestones.map((m) =>
            m.id === id ? { ...m, ...changes } : m,
        ),
    });
};

const addMilestone = () => {
    updateData({
        milestones: [
            ...props.data.milestones,
            {
                id: nanoid(8),
                phase_label: `Phase ${props.data.milestones.length + 1}`,
                title: 'New milestone',
                description: null,
                duration_label: '1 week',
                deliverables: [],
                color: '#6366f1',
            },
        ],
    });
};

const removeMilestone = (id: string) => {
    updateData({
        milestones: props.data.milestones.filter((m) => m.id !== id),
    });
};

const addDeliverable = (milestoneId: string) => {
    const m = props.data.milestones.find((m) => m.id === milestoneId);

    if (!m) {
        return;
    }

    updateMilestone(milestoneId, { deliverables: [...m.deliverables, ''] });
};

const updateDeliverable = (
    milestoneId: string,
    index: number,
    value: string,
) => {
    const m = props.data.milestones.find((m) => m.id === milestoneId);

    if (!m) {
        return;
    }

    const deliverables = [...m.deliverables];
    deliverables[index] = value;
    updateMilestone(milestoneId, { deliverables });
};

const removeDeliverable = (milestoneId: string, index: number) => {
    const m = props.data.milestones.find((m) => m.id === milestoneId);

    if (!m) {
        return;
    }

    updateMilestone(milestoneId, {
        deliverables: m.deliverables.filter((_, i) => i !== index),
    });
};
</script>

<template>
    <section class="px-8 py-6">
        <BlockTitle
            v-if="data.title || !isLocked"
            :model-value="data.title"
            placeholder="Project Timeline"
            :is-locked="isLocked"
            class="mb-6"
            @update:model-value="(v) => updateData({ title: v })"
        />

        <!-- Empty state -->
        <BlockEmptyPlaceholder
            v-if="data.milestones.length === 0 && !isLocked"
            message="No milestones yet"
            action-label="Add first milestone"
            @action="addMilestone"
        />

        <!-- Vertical layout -->
        <div v-else-if="data.layout === 'vertical'" class="relative">
            <!-- Spine line -->
            <div class="absolute top-0 left-4 h-full w-px bg-border" />

            <div class="space-y-6">
                <div
                    v-for="milestone in data.milestones"
                    :key="milestone.id"
                    class="group/milestone relative pl-12"
                >
                    <!-- Dot -->
                    <div
                        class="absolute top-1 left-2 h-5 w-5 rounded-full border-2 border-background shadow-sm"
                        :style="{ backgroundColor: milestone.color }"
                    />

                    <div
                        class="rounded-lg border border-border bg-background p-4"
                    >
                        <!-- Header row -->
                        <div
                            class="mb-2 flex items-start justify-between gap-3"
                        >
                            <div class="min-w-0 flex-1">
                                <!-- Phase label -->
                                <input
                                    v-if="!isLocked"
                                    type="text"
                                    :value="milestone.phase_label"
                                    class="mb-0.5 block w-full border-none bg-transparent text-[11px] font-semibold tracking-wider text-muted-foreground uppercase outline-none"
                                    @input="
                                        updateMilestone(milestone.id, {
                                            phase_label: (
                                                $event.target as HTMLInputElement
                                            ).value,
                                        })
                                    "
                                />
                                <span
                                    v-else
                                    class="block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    {{ milestone.phase_label }}
                                </span>

                                <!-- Title -->
                                <input
                                    v-if="!isLocked"
                                    type="text"
                                    :value="milestone.title"
                                    class="block w-full border-none bg-transparent text-sm font-semibold text-foreground outline-none"
                                    @input="
                                        updateMilestone(milestone.id, {
                                            title: (
                                                $event.target as HTMLInputElement
                                            ).value,
                                        })
                                    "
                                />
                                <h3
                                    v-else
                                    class="text-sm font-semibold text-foreground"
                                >
                                    {{ milestone.title }}
                                </h3>
                            </div>

                            <div class="flex flex-shrink-0 items-center gap-2">
                                <!-- Duration badge -->
                                <input
                                    v-if="!isLocked"
                                    type="text"
                                    :value="milestone.duration_label"
                                    class="w-20 rounded-full border border-border bg-muted/50 px-2 py-0.5 text-center text-[11px] text-muted-foreground outline-none"
                                    @input="
                                        updateMilestone(milestone.id, {
                                            duration_label: (
                                                $event.target as HTMLInputElement
                                            ).value,
                                        })
                                    "
                                />
                                <span
                                    v-else
                                    class="rounded-full bg-muted px-2 py-0.5 text-[11px] text-muted-foreground"
                                >
                                    {{ milestone.duration_label }}
                                </span>

                                <!-- Color + delete (edit only) -->
                                <div
                                    v-if="!isLocked"
                                    class="flex items-center gap-1"
                                >
                                    <div
                                        class="relative h-5 w-5 overflow-hidden rounded-full border border-border"
                                        :style="{
                                            backgroundColor: milestone.color,
                                        }"
                                    >
                                        <input
                                            type="color"
                                            :value="milestone.color"
                                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                            @input="
                                                updateMilestone(milestone.id, {
                                                    color: (
                                                        $event.target as HTMLInputElement
                                                    ).value,
                                                })
                                            "
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        class="flex h-5 w-5 items-center justify-center rounded text-muted-foreground opacity-0 transition group-hover/milestone:opacity-100 hover:text-destructive"
                                        @click="removeMilestone(milestone.id)"
                                    >
                                        <Trash2Icon class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <textarea
                            v-if="!isLocked"
                            :value="milestone.description ?? ''"
                            rows="2"
                            placeholder="Brief description of this phase…"
                            class="mb-3 w-full resize-none border-none bg-transparent text-xs text-muted-foreground outline-none placeholder:text-muted-foreground/50"
                            @input="
                                updateMilestone(milestone.id, {
                                    description:
                                        ($event.target as HTMLTextAreaElement)
                                            .value || null,
                                })
                            "
                        />
                        <p
                            v-else-if="milestone.description"
                            class="mb-3 text-xs text-muted-foreground"
                        >
                            {{ milestone.description }}
                        </p>

                        <!-- Deliverables -->
                        <div
                            v-if="
                                milestone.deliverables.length > 0 || !isLocked
                            "
                            class="space-y-1"
                        >
                            <p
                                class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Deliverables
                            </p>
                            <div class="space-y-1">
                                <div
                                    v-for="(d, di) in milestone.deliverables"
                                    :key="di"
                                    class="flex items-center gap-2"
                                >
                                    <div
                                        class="h-1 w-1 flex-shrink-0 rounded-full"
                                        :style="{
                                            backgroundColor: milestone.color,
                                        }"
                                    />
                                    <input
                                        v-if="!isLocked"
                                        type="text"
                                        :value="d"
                                        class="flex-1 border-none bg-transparent text-xs text-foreground outline-none"
                                        placeholder="Deliverable…"
                                        @input="
                                            updateDeliverable(
                                                milestone.id,
                                                di,
                                                (
                                                    $event.target as HTMLInputElement
                                                ).value,
                                            )
                                        "
                                    />
                                    <span
                                        v-else
                                        class="text-xs text-foreground"
                                        >{{ d }}</span
                                    >
                                    <button
                                        v-if="!isLocked"
                                        type="button"
                                        class="text-muted-foreground hover:text-destructive"
                                        @click="
                                            removeDeliverable(milestone.id, di)
                                        "
                                    >
                                        <XIcon class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                            <button
                                v-if="!isLocked"
                                type="button"
                                class="mt-1 flex items-center gap-1 text-[11px] text-muted-foreground hover:text-foreground"
                                @click="addDeliverable(milestone.id)"
                            >
                                <PlusIcon class="h-3 w-3" /> Add deliverable
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add milestone -->
                <div v-if="!isLocked" class="relative pl-12">
                    <div
                        class="absolute top-1 left-2 h-5 w-5 rounded-full border-2 border-dashed border-border"
                    />
                    <button
                        type="button"
                        class="flex items-center gap-2 text-xs text-muted-foreground transition hover:text-foreground"
                        @click="addMilestone"
                    >
                        <PlusIcon class="h-3.5 w-3.5" /> Add milestone
                    </button>
                </div>
            </div>
        </div>

        <!-- Horizontal layout -->
        <div v-else class="overflow-x-auto">
            <div class="flex min-w-max gap-0">
                <div
                    v-for="(milestone, index) in data.milestones"
                    :key="milestone.id"
                    class="relative min-w-40 flex-1"
                >
                    <!-- Connector line -->
                    <div class="mb-4 flex items-center">
                        <div
                            class="h-4 w-4 flex-shrink-0 rounded-full border-2 border-background shadow-sm"
                            :style="{ backgroundColor: milestone.color }"
                        />
                        <div
                            v-if="index < data.milestones.length - 1"
                            class="h-px flex-1 bg-border"
                        />
                    </div>

                    <div class="pr-4">
                        <p
                            class="text-[11px] font-semibold tracking-wider uppercase"
                            :style="{ color: milestone.color }"
                        >
                            {{ milestone.phase_label }}
                        </p>
                        <p class="text-sm font-semibold text-foreground">
                            {{ milestone.title }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            {{ milestone.duration_label }}
                        </p>
                        <p
                            v-if="milestone.description"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            {{ milestone.description }}
                        </p>
                        <ul
                            v-if="milestone.deliverables.length"
                            class="mt-2 space-y-0.5"
                        >
                            <li
                                v-for="d in milestone.deliverables"
                                :key="d"
                                class="flex items-center gap-1.5 text-xs text-muted-foreground"
                            >
                                <span
                                    class="h-1 w-1 flex-shrink-0 rounded-full"
                                    :style="{
                                        backgroundColor: milestone.color,
                                    }"
                                />
                                {{ d }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Add (edit) -->
                <button
                    v-if="!isLocked"
                    type="button"
                    class="flex min-w-32 flex-col items-center justify-center gap-1.5 rounded-lg border-2 border-dashed border-border px-4 py-6 text-xs text-muted-foreground transition hover:border-primary hover:text-foreground"
                    @click="addMilestone"
                >
                    <PlusIcon class="h-4 w-4" />
                    Add milestone
                </button>
            </div>
        </div>
    </section>
</template>
