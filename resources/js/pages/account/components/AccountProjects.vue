<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { Project } from '@/types/models/project';

const props = defineProps<{
    projects: Project[];
}>();

const formatDate = (value: string | null): string => {
    if (!value) {
return '-';
}

    return new Date(value).toLocaleDateString();
};

const progress = (project: Project): number => {
    if (!project.tasks_total) {
return 0;
}

    return Math.round((project.tasks_completed / project.tasks_total) * 100);
};
</script>

<template>
    <div v-if="props.projects.length === 0" class="py-8 text-center text-sm text-muted-foreground">
        No projects yet.
    </div>
    <Table v-else>
        <TableHeader>
            <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Start</TableHead>
                <TableHead>Due</TableHead>
                <TableHead>Progress</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="project in props.projects" :key="project.id">
                <TableCell class="font-medium">{{ project.name }}</TableCell>
                <TableCell>
                    <Badge
                        v-if="project.status"
                        :style="{
                            backgroundColor: `${project.status.color}20`,
                            color: project.status.color,
                        }"
                    >
                        {{ project.status.title }}
                    </Badge>
                </TableCell>
                <TableCell>{{ formatDate(project.start_date) }}</TableCell>
                <TableCell>{{ formatDate(project.due_date) }}</TableCell>
                <TableCell>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-24 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full bg-primary"
                                :style="{ width: `${progress(project)}%` }"
                            />
                        </div>
                        <span class="text-xs">{{ progress(project) }}%</span>
                    </div>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
