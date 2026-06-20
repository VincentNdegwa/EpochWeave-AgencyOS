<script setup lang="ts">
import {
    Activity,
    Banknote,
    CheckCircle,
    Eye,
    Pencil,
    Plus,
    Send,
    Trash,
    XCircle,
} from '@lucide/vue';
import { computed } from 'vue';

interface ActivityItem {
  id: number;
  type: string;
  description: string;
  created_at: string;
  user?: {
    id: number;
    name: string;
  } | null;
}

const props = defineProps<{
  activities: ActivityItem[];
}>();

const iconMap: Record<string, unknown> = {
  Plus,
  Pencil,
  Trash,
  Eye,
  Send,
  CheckCircle,
  XCircle,
  Banknote,
  Activity,
};

function iconFor(type: string): unknown {
  if (type.includes('created')) {
return iconMap.Plus;
}

  if (type.includes('updated')) {
return iconMap.Pencil;
}

  if (type.includes('deleted')) {
return iconMap.Trash;
}

  if (type.includes('viewed')) {
return iconMap.Eye;
}

  if (type.includes('sent')) {
return iconMap.Send;
}

  if (type.includes('accepted')) {
return iconMap.CheckCircle;
}

  if (type.includes('declined')) {
return iconMap.XCircle;
}

  if (type.includes('paid') || type.includes('payment')) {
return iconMap.Banknote;
}

  return iconMap.Activity;
}

function iconColorFor(type: string): string {
  if (type.includes('created')) {
return 'bg-green-500';
}

  if (type.includes('updated')) {
return 'bg-blue-500';
}

  if (type.includes('deleted')) {
return 'bg-red-500';
}

  if (type.includes('viewed')) {
return 'bg-purple-500';
}

  if (type.includes('sent')) {
return 'bg-indigo-500';
}

  if (type.includes('accepted')) {
return 'bg-emerald-500';
}

  if (type.includes('declined')) {
return 'bg-orange-500';
}

  if (type.includes('paid') || type.includes('payment')) {
return 'bg-cyan-500';
}

  return 'bg-gray-500';
}
</script>

<template>
  <div class="flow-root">
    <ul v-if="activities.length > 0" role="list" class="-mb-8">
      <li v-for="(activity, index) in activities" :key="activity.id">
        <div class="relative pb-8">
          <span
            v-if="index < activities.length - 1"
            class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
            aria-hidden="true"
          />
          <div class="relative flex space-x-3">
            <div>
              <span
                :class="[
                  'flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white dark:ring-gray-900',
                  iconColorFor(activity.type),
                ]"
              >
                <component
                  :is="iconFor(activity.type)"
                  class="h-4 w-4 text-white"
                  aria-hidden="true"
                />
              </span>
            </div>
            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
              <div>
                <p class="text-sm text-gray-900 dark:text-gray-100">
                  {{ activity.description }}
                </p>
              </div>
              <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                <time :datetime="activity.created_at">{{ new Date(activity.created_at).toLocaleString() }}</time>
              </div>
            </div>
          </div>
        </div>
      </li>
    </ul>
    <p v-else class="text-sm text-gray-500 dark:text-gray-400">
      No activity yet.
    </p>
  </div>
</template>
