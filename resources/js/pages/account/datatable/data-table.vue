<script setup lang="ts">
import { Building2, CalendarDays, ChevronDown, ChevronLeft, ChevronRight, Search, SlidersHorizontal } from '@lucide/vue';
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import type { ColumnDef, ColumnFiltersState, RowSelectionState, SortingState } from '@tanstack/vue-table';
import type { Account } from '@/types/models/account';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import BulkActionsToolbar from './bulk-actions-toolbar.vue';
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
import { computed } from 'vue';

const props = defineProps<{
    columns: ColumnDef<Account>[];
    data: Account[];
    searchValue?: string;
    dateFrom?: string;
    dateTo?: string;
    onSearchUpdate?: (value: string) => void;
    onDateFromUpdate?: (value: string) => void;
    onDateToUpdate?: (value: string) => void;
}>();

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<Record<string, boolean>>({});

const rowSelection = ref<RowSelectionState>({});

const selectedRows = computed(() => {
    return table.getFilteredSelectedRowModel().rows.map(row => row.original);
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
    onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
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
</script>

<template>
    <div class="w-full">
        <BulkActionsToolbar
            :selected-rows="selectedRows"
        />
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-2 py-3">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-if="onSearchUpdate"
                    placeholder="Search accounts..."
                    :model-value="searchValue"
                    class="h-9 max-w-xs pl-9 text-sm"
                    @update:model-value="onSearchUpdate(String($event))"
                />
            </div>

            <div class="flex items-center gap-2">
                <div v-if="onDateFromUpdate" class="relative flex items-center">
                    <CalendarDays class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        type="date"
                        :model-value="dateFrom"
                        class="h-9 w-40 pl-9 text-sm"
                        @update:model-value="onDateFromUpdate(String($event))"
                    />
                </div>
                <span v-if="onDateFromUpdate && onDateToUpdate" class="text-xs text-muted-foreground">—</span>
                <div v-if="onDateToUpdate" class="relative flex items-center">
                    <CalendarDays class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        type="date"
                        :model-value="dateTo"
                        class="h-9 w-40 pl-9 text-sm"
                        @update:model-value="onDateToUpdate(String($event))"
                    />
                </div>

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
                            v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                            :key="column.id"
                            class="capitalize"
                            :model-value="column.getIsVisible()"
                            @update:model-value="(value) => column.toggleVisibility(!!value)"
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
                            class="text-xs font-medium uppercase tracking-wider text-muted-foreground"
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
                            :data-state="row.getIsSelected() ? 'selected' : undefined"
                            class="transition-colors"
                        >
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-40 text-center">
                                <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                        <Building2 class="h-5 w-5" />
                                    </div>
                                    <p class="text-sm font-medium">No accounts found</p>
                                    <p class="text-xs">Try adjusting your search or filters.</p>
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
                {{ table.getFilteredRowModel().rows.length }}
                {{ table.getFilteredRowModel().rows.length === 1 ? 'account' : 'accounts' }}
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















