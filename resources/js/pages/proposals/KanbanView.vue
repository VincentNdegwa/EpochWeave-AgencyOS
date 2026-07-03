<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Calendar, Building, AlertCircle } from '@lucide/vue';
import { ref, computed } from 'vue';
import type { Proposal, ProposalStatusModel } from '@/types/models/proposal';
import ProposalActions from './components/ProposalActions.vue';

interface Props {
    proposals: Proposal[];
    proposal_statuses: ProposalStatusModel[];
    movement_rules: Record<string, number[]>;
}

const {
    proposals: allProposals,
    proposal_statuses,
    movement_rules,
} = defineProps<Props>();

const LOCKED_STATUSES = ['accepted', 'declined', 'expired'];

const dragging = ref<{
    id: number;
    trigger: string | null;
    fromStatusId: number;
} | null>(null);
const dragOverStatus = ref<string | null>(null);
const hoveredProposalId = ref<number | null>(null);
const isDragging = ref(false);
const dragStartTime = ref<number>(0);

const canDrop = (toStatusId: number): boolean => {
    if (!dragging.value) {
        return false;
    }

    const allowed = dragging.value.trigger
        ? (movement_rules[dragging.value.trigger] ?? [])
        : [];

    return (
        allowed.includes(toStatusId) &&
        dragging.value.fromStatusId !== toStatusId
    );
};

const validTargets = (trigger: string | null): number[] => {
    if (!trigger) {
        return [];
    }

    return movement_rules[trigger] ?? [];
};

const isTerminal = (trigger: string | null): boolean => {
    if (!trigger) {
        return false;
    }

    return (movement_rules[trigger] ?? []).length === 0;
};

function onDragStart(
    e: DragEvent,
    proposal: Proposal,
    status: ProposalStatusModel,
) {
    hoveredProposalId.value = null;
    isDragging.value = true;
    dragStartTime.value = Date.now();
    dragging.value = {
        id: proposal.id,
        trigger: status.automation_trigger,
        fromStatusId: status.id,
    };

    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(proposal.id));
    }
}

function onDragEnd() {
    dragging.value = null;
    dragOverStatus.value = null;
    isDragging.value = false;
}

const mouseDownTime = ref<number>(0);
const mouseDownTarget = ref<number | null>(null);

function onProposalMouseDown(proposal: Proposal) {
    mouseDownTime.value = Date.now();
    mouseDownTarget.value = proposal.id;
}

function onProposalMouseUp(proposal: Proposal) {
    const timeSinceMouseDown = Date.now() - mouseDownTime.value;

    if (
        mouseDownTarget.value === proposal.id &&
        !isDragging.value &&
        timeSinceMouseDown < 200 &&
        timeSinceMouseDown > 50
    ) {
        router.visit(`/proposals/${proposal.id}`);
    }

    mouseDownTarget.value = null;
}

function onDragOver(e: DragEvent, status: ProposalStatusModel) {
    if (canDrop(status.id)) {
        e.preventDefault();

        if (e.dataTransfer) {
            e.dataTransfer.dropEffect = 'move';
        }

        dragOverStatus.value = status.automation_trigger;
    }
}

function onDragLeave(e: DragEvent) {
    const target = e.currentTarget as HTMLElement;
    const related = e.relatedTarget as Node | null;

    if (!target.contains(related)) {
        dragOverStatus.value = null;
    }
}

async function onDrop(e: DragEvent, targetStatus: ProposalStatusModel) {
    e.preventDefault();

    if (!dragging.value || !canDrop(targetStatus.id)) {
        onDragEnd();

        return;
    }

    const proposalId = dragging.value.id;
    onDragEnd();

    router.patch(
        `/proposals/${proposalId}/move`,
        { target_status_id: targetStatus.id },
        {
            preserveScroll: true,
            onError: (e) => {
                alert(e.error ?? 'This transition is not allowed.');
            },
        },
    );
}

const columns = computed(() =>
    proposal_statuses.map((status) => {
        const trigger = status.automation_trigger;

        return {
            status,
            proposals: allProposals.filter(
                (p) => p.proposal_status_id === status.id,
            ),
            locked: trigger ? LOCKED_STATUSES.includes(trigger) : false,
        };
    }),
);

const getStatusFromId = (id: number): ProposalStatusModel | undefined =>
    proposal_statuses.find((s) => s.id === id);

const fmt = (n: number, currency = 'USD') =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
        maximumFractionDigits: 0,
    }).format(n);

const fmtDate = (s: string) =>
    new Date(s).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
</script>

<style scoped>
.hint-slide-enter-active,
.hint-slide-leave-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}
.hint-slide-enter-from,
.hint-slide-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>

<template>
    <div class="custom-scrollbar w-full overflow-x-auto pb-4">
        <div class="flex min-w-max gap-3 px-0.5 pt-0.5">
            <div
                v-for="col in columns"
                :key="col.status.id"
                class="flex w-[240px] shrink-0 flex-col rounded-xl border bg-muted/30 transition-all duration-150"
                :class="[
                    dragOverStatus === col.status.automation_trigger &&
                    canDrop(col.status.id)
                        ? 'bg-muted/30 ring-2 ring-border'
                        : '',
                    dragging &&
                    !canDrop(col.status.id) &&
                    dragging.fromStatusId !== col.status.id
                        ? 'opacity-40'
                        : '',
                ]"
                @dragover="onDragOver($event, col.status)"
                @dragleave="onDragLeave($event)"
                @drop="onDrop($event, col.status)"
            >
                <div class="flex items-center gap-2 px-3 pt-3 pb-2">
                    <div class="flex flex-1 items-center gap-2 overflow-hidden">
                        <span
                            class="h-2.5 w-2.5 shrink-0 rounded-full"
                            :style="{ backgroundColor: col.status.color }"
                        />
                        <span
                            class="truncate text-sm font-semibold text-foreground"
                            >{{ col.status.title }}</span
                        >
                    </div>

                    <div class="flex items-center gap-1">
                        <AlertCircle
                            v-if="col.locked"
                            class="h-3 w-3 text-muted-foreground"
                        />

                        <template v-if="!dragging">
                            <span
                                class="rounded-full bg-muted/50 px-1.5 py-0.5 text-xs font-semibold text-foreground tabular-nums"
                            >
                                {{ col.proposals.length }}
                            </span>
                        </template>

                        <template v-else>
                            <span
                                v-if="dragging.fromStatusId === col.status.id"
                                class="rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                            >
                                Current
                            </span>
                            <span
                                v-else-if="canDrop(col.status.id)"
                                class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700"
                            >
                                ✓ Drop here
                            </span>
                            <span
                                v-else
                                class="rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground/40"
                            >
                                ✗
                            </span>
                        </template>
                    </div>
                </div>

                <div
                    class="mx-3 mb-2 h-0.5 rounded-full"
                    :style="{ backgroundColor: col.status.color }"
                />

                <div
                    class="flex flex-1 flex-col gap-2 overflow-y-auto px-2 pb-3"
                    style="max-height: 72vh; min-height: 120px"
                >
                    <div
                        v-if="
                            col.proposals.length === 0 &&
                            dragging &&
                            canDrop(col.status.id)
                        "
                        class="flex h-16 items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 text-xs text-muted-foreground"
                    >
                        Drop here
                    </div>

                    <div
                        v-for="proposal in col.proposals"
                        :key="proposal.id"
                        class="group relative rounded-lg border bg-background p-3 shadow-sm transition-all duration-100 select-none"
                        :class="[
                            col.locked
                                ? 'cursor-pointer hover:bg-muted/50'
                                : 'cursor-grab hover:-translate-y-0.5 hover:bg-muted/30 hover:shadow-md active:cursor-grabbing',
                            dragging?.id === proposal.id
                                ? 'scale-95 opacity-40'
                                : '',
                        ]"
                        :draggable="!col.locked"
                        @mouseenter="hoveredProposalId = proposal.id"
                        @mouseleave="hoveredProposalId = null"
                        @dragstart="onDragStart($event, proposal, col.status)"
                        @dragend="onDragEnd"
                        @mousedown="onProposalMouseDown(proposal)"
                        @mouseup="onProposalMouseUp(proposal)"
                    >
                        <div
                            class="mb-1.5 flex items-start justify-between gap-1"
                        >
                            <span
                                class="font-mono text-xs text-muted-foreground"
                            >
                                #{{ proposal.proposal_number }}
                            </span>
                            <ProposalActions
                                :proposal="proposal"
                                :proposal_statuses="proposal_statuses"
                                variant="dropdown"
                                size="icon"
                            />
                        </div>

                        <p
                            class="mb-2 line-clamp-2 text-sm leading-snug font-medium text-foreground"
                        >
                            {{ proposal.title }}
                        </p>

                        <div
                            v-if="proposal.account"
                            class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground"
                        >
                            <Building class="h-3 w-3 shrink-0" />
                            <span class="truncate">{{
                                proposal.account.company_name
                            }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between gap-2 border-t border-border/50 pt-1.5"
                        >
                            <span
                                class="text-xs font-semibold text-foreground tabular-nums"
                            >
                                {{
                                    fmt(
                                        Number(
                                            proposal.grand_total ||
                                                proposal.total_value ||
                                                0,
                                        ),
                                        proposal.currency ?? 'USD',
                                    )
                                }}
                            </span>
                            <div
                                class="flex items-center gap-1 text-xs text-muted-foreground"
                            >
                                <Calendar class="h-3 w-3 shrink-0" />
                                <span>{{ fmtDate(proposal.created_at) }}</span>
                            </div>
                        </div>

                        <Transition name="hint-slide">
                            <div
                                v-if="
                                    hoveredProposalId === proposal.id &&
                                    !dragging
                                "
                                class="mt-2 border-t border-border/30 pt-2"
                            >
                                <div
                                    v-if="
                                        isTerminal(
                                            col.status.automation_trigger,
                                        )
                                    "
                                    class="text-[10px] text-muted-foreground italic"
                                >
                                    Final status — cannot be moved
                                </div>
                                <div
                                    v-else
                                    class="flex flex-wrap items-center gap-1"
                                >
                                    <span
                                        class="mr-0.5 text-[10px] leading-none text-muted-foreground"
                                        >Move to:</span
                                    >
                                    <span
                                        v-for="targetId in validTargets(
                                            col.status.automation_trigger,
                                        )"
                                        :key="targetId"
                                        class="inline-flex items-center gap-1 rounded-full bg-muted/50 px-1.5 py-0.5 text-[10px] leading-none font-semibold text-muted-foreground"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :style="{
                                                backgroundColor:
                                                    getStatusFromId(targetId)
                                                        ?.color || '#6b7280',
                                            }"
                                        />
                                        {{ getStatusFromId(targetId)?.title }}
                                    </span>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <div
                        v-if="col.proposals.length === 0 && !dragging"
                        class="flex h-16 items-center justify-center text-xs text-muted-foreground/50"
                    >
                        No proposals
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
