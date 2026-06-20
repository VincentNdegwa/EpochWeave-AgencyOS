<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Calendar, Building, AlertCircle } from '@lucide/vue';
import { ref, computed } from 'vue';
import type { Invoice } from '@/types/models/invoice';
import type { InvoiceStatus } from '@/types/models/invoice_status';
import InvoiceActions from './components/InvoiceActions.vue';

interface StatusColumn {
    id: number;
    key: string;
    label: string;
    color: string;
    locked: boolean;
}

const { invoices: allInvoices, invoice_statuses } = defineProps<{
    invoices: Invoice[];
    invoice_statuses: InvoiceStatus[];
}>();

const LOCKED_STATUSES = ['paid', 'overdue'];

const statusColumns = computed<StatusColumn[]>(() =>
    invoice_statuses.map((s) => ({
        id: s.id,
        key: s.automation_trigger ?? s.title,
        label: s.title,
        color: s.color,
        locked: LOCKED_STATUSES.includes(s.automation_trigger ?? ''),
    })),
);

const dragging = ref<{
    id: number;
    fromStatus: string;
} | null>(null);
const dragOverStatus = ref<string | null>(null);
const hoveredInvoiceId = ref<number | null>(null);
const isDragging = ref(false);

const canDrop = (toStatusKey: string): boolean => {
    if (!dragging.value) {
return false;
}

    const targetCol = statusColumns.value.find((c) => c.key === toStatusKey);

    if (!targetCol || targetCol.locked) {
return false;
}

    return dragging.value.fromStatus !== toStatusKey;
};

const isLocked = (statusKey: string): boolean => {
    return LOCKED_STATUSES.includes(statusKey);
};

function onDragStart(e: DragEvent, invoice: Invoice) {
    hoveredInvoiceId.value = null;
    isDragging.value = true;
    dragging.value = {
        id: invoice.id,
        fromStatus: invoice.invoice_status?.automation_trigger ?? 'draft',
    };

    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(invoice.id));
    }
}

function onDragEnd() {
    dragging.value = null;
    dragOverStatus.value = null;
    isDragging.value = false;
}

const mouseDownTime = ref<number>(0);
const mouseDownTarget = ref<number | null>(null);

function onInvoiceMouseDown(invoice: Invoice) {
    mouseDownTime.value = Date.now();
    mouseDownTarget.value = invoice.id;
}

function onInvoiceMouseUp(invoice: Invoice) {
    const timeSinceMouseDown = Date.now() - mouseDownTime.value;

    if (
        mouseDownTarget.value === invoice.id &&
        !isDragging.value &&
        timeSinceMouseDown < 200 &&
        timeSinceMouseDown > 50
    ) {
        router.visit(`/invoices/${invoice.id}`);
    }

    mouseDownTarget.value = null;
}

function onDragOver(e: DragEvent, statusKey: string) {
    if (canDrop(statusKey)) {
        e.preventDefault();

        if (e.dataTransfer) {
            e.dataTransfer.dropEffect = 'move';
        }

        dragOverStatus.value = statusKey;
    }
}

function onDragLeave(e: DragEvent) {
    const target = e.currentTarget as HTMLElement;
    const related = e.relatedTarget as Node | null;

    if (!target.contains(related)) {
        dragOverStatus.value = null;
    }
}

async function onDrop(e: DragEvent, targetStatus: StatusColumn) {
    e.preventDefault();

    if (!dragging.value || !canDrop(targetStatus.key)) {
        onDragEnd();

        return;
    }

    const invoiceId = dragging.value.id;
    onDragEnd();

    router.patch(
        `/invoices/${invoiceId}/status`,
        { invoice_status_id: targetStatus.id },
        {
            preserveScroll: true,
            onError: (err) => {
                alert(err.error ?? 'This status transition is not allowed.');
            },
        },
    );
}

const columns = computed(() =>
    statusColumns.value.map((status) => ({
        status,
        invoices: allInvoices.filter((i) => i.invoice_status?.automation_trigger === status.key),
    })),
);

const fmt = (n: number, currency = 'USD') =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency, maximumFractionDigits: 0 }).format(n);

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
                :key="col.status.key"
                class="flex w-[240px] shrink-0 flex-col rounded-xl border bg-muted/30 transition-all duration-150"
                :class="[
                    dragOverStatus === col.status.key && canDrop(col.status.key)
                        ? 'ring-2 ring-border bg-muted/30'
                        : '',
                    dragging &&
                    !canDrop(col.status.key) &&
                    dragging.fromStatus !== col.status.key
                        ? 'opacity-40'
                        : '',
                ]"
                @dragover="onDragOver($event, col.status.key)"
                @dragleave="onDragLeave($event)"
                @drop="onDrop($event, col.status)"
            >
                <div class="flex items-center gap-2 px-3 pt-3 pb-2">
                    <div class="flex flex-1 items-center gap-2 overflow-hidden">
                        <span
                            class="h-2.5 w-2.5 shrink-0 rounded-full"
                            :style="{ backgroundColor: col.status.color }"
                        />
                        <span class="truncate text-sm font-semibold text-foreground">
                            {{ col.status.label }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1">
                        <AlertCircle
                            v-if="col.status.locked"
                            class="h-3 w-3 text-muted-foreground"
                        />

                        <template v-if="!dragging">
                            <span
                                class="rounded-full bg-muted/50 px-1.5 py-0.5 text-xs font-semibold tabular-nums text-foreground"
                            >
                                {{ col.invoices.length }}
                            </span>
                        </template>

                        <template v-else>
                            <span
                                v-if="dragging.fromStatus === col.status.key"
                                class="rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                            >
                                Current
                            </span>
                            <span
                                v-else-if="canDrop(col.status.key)"
                                class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700"
                            >
                                Drop here
                            </span>
                            <span
                                v-else
                                class="rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground/40"
                            >
                                Locked
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
                            col.invoices.length === 0 &&
                            dragging &&
                            canDrop(col.status.key)
                        "
                        class="flex h-16 items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 text-xs text-muted-foreground"
                    >
                        Drop here
                    </div>

                    <div
                        v-for="invoice in col.invoices"
                        :key="invoice.id"
                        class="group relative rounded-lg border bg-background p-3 shadow-sm transition-all duration-100 select-none"
                        :class="[
                            col.status.locked
                                ? 'cursor-pointer hover:bg-muted/50'
                                : 'cursor-grab hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing hover:bg-muted/30',
                            dragging?.id === invoice.id
                                ? 'scale-95 opacity-40'
                                : '',
                        ]"
                        :draggable="!col.status.locked"
                        @mouseenter="hoveredInvoiceId = invoice.id"
                        @mouseleave="hoveredInvoiceId = null"
                        @dragstart="onDragStart($event, invoice)"
                        @dragend="onDragEnd"
                        @mousedown="onInvoiceMouseDown(invoice)"
                        @mouseup="onInvoiceMouseUp(invoice)"
                    >
                        <div class="mb-1.5 flex items-start justify-between gap-1">
                            <span class="font-mono text-xs text-muted-foreground">
                                #{{ invoice.invoice_number || `INV-${invoice.id}` }}
                            </span>
                            <InvoiceActions
                                :invoice="invoice"
                                :invoice_statuses="invoice_statuses"
                                variant="dropdown"
                                size="icon"
                            />
                        </div>

                        <p class="mb-2 line-clamp-2 text-sm leading-snug font-medium text-foreground">
                            {{ invoice.account?.company_name || 'No account' }}
                        </p>

                        <div
                            v-if="invoice.user"
                            class="mb-1.5 flex items-center gap-1.5 text-xs text-muted-foreground"
                        >
                            <Building class="h-3 w-3 shrink-0" />
                            <span class="truncate">{{ invoice.user.name }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between gap-2 border-t border-border/50 pt-1.5"
                        >
                            <span class="text-xs font-semibold text-foreground tabular-nums">
                                {{ fmt(Number(invoice.grand_total || 0), invoice.currency ?? 'USD') }}
                            </span>
                            <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                <Calendar class="h-3 w-3 shrink-0" />
                                <span>{{ fmtDate(invoice.due_date || invoice.created_at) }}</span>
                            </div>
                        </div>

                        <Transition name="hint-slide">
                            <div
                                v-if="hoveredInvoiceId === invoice.id && !dragging"
                                class="mt-2 border-t border-border/30 pt-2"
                            >
                                <div
                                    v-if="col.status.locked"
                                    class="text-[10px] text-muted-foreground italic"
                                >
                                    System-controlled — cannot be moved
                                </div>
                                <div
                                    v-else
                                    class="text-[10px] text-muted-foreground italic"
                                >
                                    Drag to change status
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <div
                        v-if="col.invoices.length === 0 && !dragging"
                        class="flex h-16 items-center justify-center text-xs text-muted-foreground/50"
                    >
                        No invoices
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
