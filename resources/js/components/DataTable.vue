<script setup lang="ts" generic="TData extends object">
import {
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Search,
    SlidersHorizontal,
} from '@lucide/vue';
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import type {
    ColumnDef,
    ColumnFiltersState,
    RowSelectionState,
    SortingState,
} from '@tanstack/vue-table';
import { ref, computed } from 'vue';
import type { Component, VNode } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { valueUpdater } from '@/components/ui/table/utils';

interface FilterConfig {
    key: string;
    type: 'search' | 'date' | 'select';
    placeholder?: string;
    label?: string;
}

interface Props {
    columns: ColumnDef<TData>[];
    data: TData[];
    filters?: Record<string, string | undefined>;
    onFilter?: (filters: Record<string, string | undefined>) => void;
    filterConfigs?: FilterConfig[];
    searchPlaceholder?: string;
    emptyIcon?: Component;
    emptyTitle?: string;
    emptyDescription?: string;
    itemName?: string;
    itemNamePlural?: string;
    bulkActions?: Component;
    toolbarActions?: VNode;
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({}),
    searchPlaceholder: 'Search...',
    emptyTitle: 'No items found',
    emptyDescription: 'Try adjusting your search or filters.',
    itemName: 'item',
    itemNamePlural: 'items',
});

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<Record<string, boolean>>({});
const rowSelection = ref<RowSelectionState>({});

const selectedRows = computed(() => {
    return table.getFilteredSelectedRowModel().rows.map((row) => row.original);
});

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, rowSelection),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
    },
});

const rowCount = computed(() => table.getFilteredRowModel().rows.length);

const hasSearchFilter = computed(() =>
    props.filterConfigs?.some((c) => c.type === 'search'),
);

const searchConfig = computed(() =>
    props.filterConfigs?.find((c) => c.type === 'search'),
);

function updateFilter(key: string, value: string | undefined) {
    const newFilters = { ...props.filters, [key]: value };
    props.onFilter?.(newFilters);
}
</script>

<template>
    <div class="w-full">
        <!-- Bulk Actions Slot -->
        <component
            :is="bulkActions"
            v-if="bulkActions"
            :selected-rows="selectedRows"
        />

        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-2 py-3">
            <div class="relative flex-1">
                <Search
                    v-if="hasSearchFilter"
                    class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-if="hasSearchFilter"
                    :placeholder="searchConfig?.placeholder || searchPlaceholder"
                    :model-value="filters?.search"
                    class="h-9 max-w-xs pl-9 text-sm"
                    @update:model-value="updateFilter('search', $event || undefined)"
                />
            </div>

            <div class="flex items-center gap-2">
                <!-- Dynamic Filters -->
                <template v-for="config in filterConfigs" :key="config.key">
                    <Input
                        v-if="config.type === 'date'"
                        type="date"
                        :placeholder="config.placeholder"
                        :model-value="filters?.[config.key]"
                        class="h-9 w-40 text-sm"
                        @update:model-value="updateFilter(config.key, $event || undefined)"
                    />
                </template>

                <!-- Custom Toolbar Actions Slot -->
                <component :is="toolbarActions" v-if="toolbarActions" />

                <!-- Column Visibility -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm" class="h-9 gap-2">
                            <SlidersHorizontal class="h-3.5 w-3.5" />
                            Columns
                            <ChevronDown class="h-3.5 w-3.5 opacity-60" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-40">
                        <DropdownMenuCheckboxItem
                            v-for="column in table
                                .getAllColumns()
                                .filter((column) => column.getCanHide())"
                            :key="column.id"
                            class="capitalize"
                            :model-value="column.getIsVisible()"
                            @update:model-value="
                                (value) => column.toggleVisibility(!!value)
                            "
                        >
                            {{ column.id.replace('_', ' ') }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                        class="bg-muted/30 hover:bg-muted/30"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="
                                row.getIsSelected() ? 'selected' : undefined
                            "
                            class="transition-colors"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell
                                :colspan="columns.length"
                                class="h-40 text-center"
                            >
                                <div
                                    class="flex flex-col items-center gap-2 text-muted-foreground"
                                >
                                    <div
                                        v-if="emptyIcon"
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-muted"
                                    >
                                        <component :is="emptyIcon" class="h-5 w-5" />
                                    </div>
                                    <p class="text-sm font-medium">
                                        {{ emptyTitle }}
                                    </p>
                                    <p class="text-xs">
                                        {{ emptyDescription }}
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between py-3">
            <p class="text-xs text-muted-foreground">
                {{ rowCount }}
                {{ rowCount === 1 ? itemName : itemNamePlural }}
            </p>
            <div class="flex items-center gap-1">
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 p-0"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 p-0"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
