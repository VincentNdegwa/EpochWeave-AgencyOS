<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, MoreHorizontal, Copy, Star, Trash2, Edit, Eye } from '@lucide/vue';
import { ref } from 'vue';
import ProposalTemplateController from '@/actions/App/Http/Controllers/ProposalTemplateController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import proposalTemplates from '@/routes/proposal-templates';
import type { ProposalTemplate } from '@/types/models/proposal';

const props = defineProps<{
  templates: ProposalTemplate[];
  defaultTemplate: ProposalTemplate | null;
}>();

const handleSetDefault = (templateId: number) => {
  router.post(ProposalTemplateController.setDefault(templateId).url);
};

const handleDuplicate = (templateId: number) => {
  router.post(ProposalTemplateController.duplicate(templateId).url);
};

const handleDelete = (templateId: number) => {
  if (confirm('Are you sure you want to delete this template?')) {
    router.delete(ProposalTemplateController.destroy(templateId).url);
  }
};

defineOptions({
  layout: {
    title: 'templates',
    description: 'Manage reusable proposal templates and content blocks.',
    breadcrumbs: [
      { title: 'dashboard', href: dashboard() },
      { title: 'templates' },
    ],
  },
});
</script>

<template>
  <Head title="Proposal Templates" />
  
  <div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Proposal Templates</h1>
        <p class="text-muted-foreground">Manage reusable proposal templates and content blocks.</p>
      </div>
      <div class="flex gap-2">
        <Link :href="proposalTemplates.create()">
          <Button>
            <Plus class="mr-2 h-4 w-4" />
            New Template
          </Button>
        </Link>
      </div>
    </div>

    <div v-if="templates.length === 0" class="flex flex-col items-center justify-center py-12">
      <div class="text-muted-foreground text-6xl mb-4">📄</div>
      <h3 class="text-lg font-medium text-foreground mb-2">No templates yet</h3>
      <p class="text-muted-foreground mb-6">Get started by creating your first proposal template.</p>
      <Link :href="proposalTemplates.create()">
        <Button>
          <Plus class="mr-2 h-4 w-4" />
          Create Template
        </Button>
      </Link>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card
        v-for="template in templates"
        :key="template.id"
        class="hover:shadow-md transition-shadow"
      >
        <CardHeader class="pb-3">
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <CardTitle class="text-lg truncate">{{ template.name }}</CardTitle>
              <CardDescription v-if="template.description" class="mt-1 line-clamp-2">
                {{ template.description }}
              </CardDescription>
            </div>
            <div v-if="template.is_default" class="ml-3 flex-shrink-0">
              <Badge variant="secondary" class="bg-green-100 text-green-800 hover:bg-green-200">
                Default
              </Badge>
            </div>
          </div>
        </CardHeader>

        <CardContent class="pb-3">
          <div class="text-sm text-muted-foreground">
            Updated {{ new Date(template.updated_at).toLocaleDateString() }}
          </div>
        </CardContent>

        <CardFooter class="pt-0">
          <div class="flex items-center justify-between w-full">
            <div class="flex gap-2">
              <Link :href="proposalTemplates.show(template.id)">
                <Button variant="ghost" size="sm">
                  <Eye class="h-4 w-4 mr-1" />
                  View
                </Button>
              </Link>
              <Link :href="proposalTemplates.edit(template.id)">
                <Button variant="ghost" size="sm">
                  <Edit class="h-4 w-4 mr-1" />
                  Edit
                </Button>
              </Link>
            </div>

            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm">
                  <MoreHorizontal class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem @click="handleDuplicate(template.id)">
                  <Copy class="mr-2 h-4 w-4" />
                  Duplicate
                </DropdownMenuItem>
                <DropdownMenuItem 
                  @click="handleSetDefault(template.id)"
                  v-if="!template.is_default"
                >
                  <Star class="mr-2 h-4 w-4" />
                  Set as Default
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem 
                  @click="handleDelete(template.id)"
                  class="text-destructive"
                >
                  <Trash2 class="mr-2 h-4 w-4" />
                  Delete
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>
