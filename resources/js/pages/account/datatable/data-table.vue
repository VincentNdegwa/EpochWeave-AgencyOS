<script setup lang="ts" generic="TData, TValue">
import { ChevronDown } from '@lucide/vue';
import {
    FlexRender,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import type { SortingState, ColumnFiltersState } from '@tanstack/vue-table';
import { ref } from 'vue';
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

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
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
    },
});
</script>

<template>
    <div class="w-full">
        <div class="flex items-center gap-2 py-4">
            <div class="flex items-center gap-2 flex-1">
                <Input
                    v-if="onSearchUpdate"
                    placeholder="Search by company name..."
                    :model-value="searchValue"
                    class="max-w-sm"
                    @update:model-value="onSearchUpdate(String($event))"
                />
                <div v-if="onDateFromUpdate" class="flex items-center gap-2">
                    <span class="text-sm text-muted-foreground">From:</span>
                    <Input
                        type="date"
                        :model-value="dateFrom"
                        class="w-40"
                        @update:model-value="onDateFromUpdate(String($event))"
                    />
                </div>
                <div v-if="onDateToUpdate" class="flex items-center gap-2">
                    <span class="text-sm text-muted-foreground">To:</span>
                    <Input
                        type="date"
                        :model-value="dateTo"
                        class="w-40"
                        @update:model-value="onDateToUpdate(String($event))"
                    />
                </div>
            </div>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline">
                        Columns
                        <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
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
                        {{ column.id }}
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
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
                                class="h-24 text-center"
                            >
                                No results.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
        <div class="flex items-center justify-end space-x-2 py-4">
            <div class="flex-1 text-sm text-muted-foreground">
                {{ table.getFilteredRowModel().rows.length }} row(s).
            </div>
            <div class="space-x-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    Previous
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>
