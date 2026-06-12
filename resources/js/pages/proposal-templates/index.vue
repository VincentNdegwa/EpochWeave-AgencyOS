<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, MoreHorizontal, Copy, Star, Trash2, Edit, Eye } from '@lucide/vue';
import ProposalTemplateController from '@/actions/App/Http/Controllers/ProposalTemplateController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { dashboard } from '@/routes';
import proposalTemplates from '@/routes/proposal-templates';
import type { ProposalTemplate } from '@/types/models/proposal';

defineProps<{
  templates: ProposalTemplate[];
  defaultTemplate: ProposalTemplate | null;
}>();

const DEFAULT_THUMBNAIL_URL = 'https://placehold.co/400x300/e2e8f0/64748b?text=Proposal+Template';


const handleSetDefault = (templateId: number) => {
  router.post(ProposalTemplateController.setDefault(templateId).url);
};

const handleDuplicate = (templateId: number) => {
  router.post(ProposalTemplateController.duplicate(templateId).url);
};

const handleDelete = async (templateId: number) => {
  const { confirm } = await import('@/composables/useConfirmation');
  
  if (await confirm({
    title: 'Delete Template',
    description: 'Are you sure you want to delete this template? This action cannot be undone.',
    confirmText: 'Delete',
    cancelText: 'Cancel',
    variant: 'destructive',
  })) {
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
  
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <div>
        <h4 class="font-bold tracking-tight">Proposal Templates</h4>
        <p class="text-muted-foreground">Manage reusable proposal templates and content blocks.</p>
      </div>
      <div class="flex gap-2">
        <Link :href="proposalTemplates.create()">
          <Button type="button">
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

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <Card
        v-for="template in templates"
        :key="template.id"
        class="hover:shadow-md transition-shadow overflow-hidden max-w-sm"
      >
        <!-- Thumbnail Section -->
        <div class="aspect-[4/3] bg-muted border-b relative overflow-hidden">
          <img 
            :src="template.thumbnail_url || DEFAULT_THUMBNAIL_URL" 
            :alt="template.name"
            class="w-full h-full object-cover"
            @error="(e: Event) => { const target = e.target as HTMLImageElement; target.src = DEFAULT_THUMBNAIL_URL; }"
          />
          <div v-if="template.is_default" class="absolute top-2 right-2">
            <Badge variant="secondary" class="bg-green-100 text-green-800 hover:bg-green-200 text-xs">
              Default
            </Badge>
          </div>
        </div>

        <CardHeader class="pb-2 pt-3">
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <CardTitle class="text-sm font-medium truncate">{{ template.name }}</CardTitle>
              <CardDescription v-if="template.description" class="mt-1 line-clamp-2 text-xs">
                {{ template.description }}
              </CardDescription>
            </div>
          </div>
        </CardHeader>

        <CardContent class="pb-2">
          <div class="text-xs text-muted-foreground">
            Updated {{ new Date(template.updated_at).toLocaleDateString() }}
          </div>
        </CardContent>

        <CardFooter class="pt-0">
          <div class="flex items-center justify-between w-full">
            <div class="flex gap-1">
              <Link :href="proposalTemplates.show(template.id)">
                <Button variant="ghost" size="sm" class="h-7 px-2">
                  <Eye class="h-3 w-3 mr-1" />
                  <span class="text-xs">View</span>
                </Button>
              </Link>
              <Link :href="proposalTemplates.edit(template.id)">
                <Button variant="ghost" size="sm" class="h-7 px-2">
                  <Edit class="h-3 w-3 mr-1" />
                  <span class="text-xs">Edit</span>
                </Button>
              </Link>
            </div>

            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm" class="h-7 w-7 p-0">
                  <MoreHorizontal class="h-3 w-3" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem @click="handleDuplicate(template.id)">
                  <Copy class="mr-2 h-3 w-3" />
                  Duplicate
                </DropdownMenuItem>
                <DropdownMenuItem 
                  @click="handleSetDefault(template.id)"
                  v-if="!template.is_default"
                >
                  <Star class="mr-2 h-3 w-3" />
                  Set as Default
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem 
                  @click="handleDelete(template.id)"
                  class="text-destructive"
                >
                  <Trash2 class="mr-2 h-3 w-3" />
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
