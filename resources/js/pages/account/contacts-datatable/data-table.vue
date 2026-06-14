<script setup lang="ts" generic="TData, TValue">
import { Users } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    emptyTitle?: string;
    emptyDescription?: string;
}>();

const emptyTitle = props.emptyTitle ?? 'No contacts found';
const emptyDescription = props.emptyDescription ?? 'No contacts available for this account.';

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <div class="w-full">
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
                            class="h-32 text-center"
                        >
                            <div
                                class="flex flex-col items-center gap-2 text-muted-foreground"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-muted"
                                >
                                    <Users class="h-5 w-5" />
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
</template>
