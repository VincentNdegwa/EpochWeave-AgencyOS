<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, ArrowLeft, Users, Clock, LayoutList, Layers } from '@lucide/vue';
import { ref } from 'vue';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useProjectStatuses } from '@/composables/useEnums';
import type { Project } from '@/types/models/project';
import type { Task } from '@/types/models/task';
import ProjectFormDialog from './dialogs/ProjectFormDialog.vue';
import KanbanView from '../tasks/KanbanView.vue';
import TaskDataTable from '../tasks/datatable/data-table.vue';
import { createColumns as createTaskColumns } from '../tasks/datatable/columns';

const { project } = defineProps<{ project: Project }>();
const projectStatuses = useProjectStatuses();
const status = projectStatuses.getByValue(project.status);
const editOpen = ref(false);

// Control mode inside the task workspace tab scope
const taskViewMode = ref<'kanban' | 'list'>('kanban');

const progress = project.tasks_total > 0
    ? Math.round((project.tasks_completed / project.tasks_total) * 100)
    : 0;

const handleEditSuccess = () => {
    editOpen.value = false;
    router.reload();
};

const taskColumns = createTaskColumns(
    (task: Task) => {
        router.visit(`/tasks/${task.id}/edit`);
    },
    (task: Task) => {
        router.delete(`/tasks/${task.id}`);
    },
);
</script>

<template>
    <Head :title="project.name" />

    <div class="space-y-6">
        <!-- 1. Global Meta Header Layout -->
        <div class="flex items-center justify-between border-b border-border pb-4">
            <div class="flex items-center gap-3">
                <Link :href="ProjectController.index().url">
                    <Button variant="ghost" size="icon" class="h-8 w-8">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <div class="flex items-center gap-2.5">
                    <span
                        class="h-3.5 w-3.5 rounded-full ring-2 ring-offset-2 ring-offset-background"
                        :style="{ backgroundColor: project.color || '#6366f1' }"
                    />
                    <h1 class="text-xl font-bold tracking-tight text-foreground">{{ project.name }}</h1>
                    <Badge
                        :style="{ backgroundColor: status?.hexColor || '#6b7280', color: '#fff' }"
                        class="rounded-md text-[11px] font-semibold uppercase tracking-wider"
                    >
                        {{ status?.label || project.status }}
                    </Badge>
                </div>
            </div>
            <Button size="sm" variant="outline" class="gap-1.5" @click="editOpen = true">
                <Pencil class="h-3.5 w-3.5" />
                Edit Project
            </Button>
        </div>

        <!-- 2. Dual Column Layout Split: Workspace vs Meta Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            <!-- Left Workspace Window Pane -->
            <div class="lg:col-span-3">
                <Tabs default-value="tasks" class="w-full space-y-4">
                    <TabsList class="border-b border-border bg-transparent p-0 h-auto w-full justify-start rounded-none gap-2">
                        <TabsTrigger 
                            value="tasks" 
                            class="data-[state=active]:border-slate-900 data-[state=active]:bg-transparent data-[state=active]:shadow-none rounded-none border-b-2 border-transparent px-4 py-2.5 text-sm font-medium transition-all -mb-px"
                        >
                            <Layers class="h-4 w-4 mr-2" />
                            Tasks Tracker
                        </TabsTrigger>
                        <TabsTrigger 
                            value="overview" 
                            class="data-[state=active]:border-slate-900 data-[state=active]:bg-transparent data-[state=active]:shadow-none rounded-none border-b-2 border-transparent px-4 py-2.5 text-sm font-medium transition-all -mb-px"
                        >
                            <LayoutList class="h-4 w-4 mr-2" />
                            Overview & Specs
                        </TabsTrigger>
                        <TabsTrigger 
                            value="team" 
                            class="data-[state=active]:border-slate-900 data-[state=active]:bg-transparent data-[state=active]:shadow-none rounded-none border-b-2 border-transparent px-4 py-2.5 text-sm font-medium transition-all -mb-px"
                        >
                            <Users class="h-4 w-4 mr-2" />
                            Team Members
                        </TabsTrigger>
                    </TabsList>

                    <!-- TASKS TRACKER WORKSPACE TAB -->
                    <TabsContent value="tasks" class="space-y-4 outline-none">
                        <!-- Scope Sub-Toolbar for View Swapping -->
                        <div class="flex justify-end gap-1 p-1 border border-border bg-muted/40 rounded-lg max-w-fit ml-auto">
                            <Button 
                                size="sm" 
                                :variant="taskViewMode === 'kanban' ? 'secondary' : 'ghost'" 
                                class="h-7 px-3 text-xs font-medium"
                                @click="taskViewMode = 'kanban'"
                            >
                                Kanban Board
                            </Button>
                            <Button 
                                size="sm" 
                                :variant="taskViewMode === 'list' ? 'secondary' : 'ghost'" 
                                class="h-7 px-3 text-xs font-medium"
                                @click="taskViewMode = 'list'"
                            >
                                List Ledger
                            </Button>
                        </div>

                        <!-- Isolated Horizontal Scroll Containment Boundary -->
                        <div v-if="taskViewMode === 'kanban'" class="w-full overflow-x-auto pb-4 scrollbar-thin">
                            <KanbanView :tasks="project.tasks || []" class="min-w-[850px] xl:min-w-full" />
                        </div>
                        
                        <div v-else class="w-full">
                            <TaskDataTable :columns="taskColumns" :data="project.tasks || []" />
                        </div>
                    </TabsContent>

                    <!-- OVERVIEW / METADATA SUMMARY TAB -->
                    <TabsContent value="overview" class="space-y-4 outline-none">
                        <div class="rounded-xl border bg-card text-card-foreground p-5 space-y-4">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Description</h3>
                                <p class="mt-2 text-sm leading-relaxed text-foreground/90 whitespace-pre-wrap">
                                    {{ project.description || 'No descriptive summary provided for this project infrastructure.' }}
                                </p>
                            </div>
                            
                            <div class="h-px bg-border" />
                            
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">Associated Client Target</h3>
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ project.account?.company_name || 'Internal Corporate Operations' }}
                                </span>
                            </div>
                        </div>
                    </TabsContent>

                    <!-- TEAM PIVOT ROSTER TAB -->
                    <TabsContent value="team" class="outline-none">
                        <div class="rounded-xl border bg-white overflow-hidden">
                            <div class="divide-y divide-slate-100">
                                <div
                                    v-for="member in project.members"
                                    :key="member.id"
                                    class="flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-muted text-xs font-bold text-slate-700 border border-border">
                                            {{ member.user?.name?.charAt(0) || '?' }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-foreground">{{ member.user?.name || 'Unknown Operator' }}</p>
                                            <p class="text-xs text-muted-foreground capitalize mt-0.5">{{ member.role || 'Project Participant' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right" v-if="member.hourly_rate">
                                        <span class="font-mono text-xs font-semibold text-slate-800 bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                            ${{ member.hourly_rate }}/hr
                                        </span>
                                    </div>
                                </div>
                                
                                <div v-if="!project.members?.length" class="px-5 py-8 text-center text-sm text-muted-foreground">
                                    No team resources assigned to this project instance loop.
                                </div>
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>

            <!-- Right Sidebar Panel (Static Metrics Performance Dashboard) -->
            <div class="rounded-xl border bg-card text-card-foreground p-5 space-y-5 lg:sticky lg:top-24">
                <!-- Progress Block -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-muted-foreground uppercase tracking-wider text-[10px]">Delivery Progress</span>
                        <span class="font-bold text-foreground font-mono">{{ progress }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-muted w-full">
                        <div class="h-full rounded-full bg-slate-900 transition-all duration-500" :style="{ width: `${progress}%` }" />
                    </div>
                </div>

                <div class="h-px bg-border" />

                <!-- Allocation Counters -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block">Completed</span>
                        <span class="text-lg font-bold text-slate-900 font-mono block mt-1">{{ project.tasks_completed }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block">Total Scope</span>
                        <span class="text-lg font-medium text-slate-400 font-mono block mt-1">{{ project.tasks_total }}</span>
                    </div>
                </div>

                <div class="h-px bg-border" />

                <!-- Budgetary & Ledger Targets -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block">Currency Context</span>
                        <span class="text-sm font-semibold text-foreground block mt-1 uppercase">{{ project.currency }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block">Hourly Parameters</span>
                        <span class="text-sm font-mono font-semibold text-foreground block mt-1">
                            ${{ project.hourly_rate || '0' }}/h
                        </span>
                    </div>
                </div>

                <div class="h-px bg-border" />

                <!-- Structural Deadlines Timeline Panel -->
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Start Target</span>
                        <span class="font-mono font-medium text-foreground">{{ project.start_date || '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Due Target</span>
                        <span class="font-mono font-semibold text-foreground">{{ project.due_date || '—' }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Hidden Dialog Modals Stack -->
        <ProjectFormDialog v-model:open="editOpen" :project="project" @success="handleEditSuccess" />
    </div>
</template>