<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import {
    BellIcon,
    MailIcon,
    EyeIcon,
    RefreshCwIcon,
    CheckCircleIcon,
    XCircleIcon,
    SparklesIcon,
} from '@lucide/vue';
import { computed } from 'vue';
import notifications from '@/routes/workspace-settings/notifications';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { dashboard } from '@/routes';
import { index } from '@/routes/workspace-settings';
import type { NotificationSettings } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Business settings', href: index() },
            { title: 'Notifications' },
        ],
    },
});

const page = usePage();

const notificationSettings = computed(
    () => page.props.notificationSettings as NotificationSettings | null,
);


const syncToggle = (path: string) => computed({
    get: () => {
        if (!notificationSettings.value?.settings) return false;
        
        return path.split('.').reduce((acc: any, key) => acc?.[key], notificationSettings.value.settings) ?? false;
    },
    set: (value: boolean) => {
        if (!notificationSettings.value?.settings) return;

        const keys = path.split('.');
        let current = notificationSettings.value.settings as any;

        for (let i = 0; i < keys.length - 1; i++) {
            const key = keys[i];
            if (!current[key]) current[key] = {}; // Create nested objects inline if they don't exist
            current = current[key];
        }

        current[keys[keys.length - 1]] = value;
    }
});


const proposalToggles = [
    {
        id: 'viewed',
        title: 'Proposal Viewed',
        description: 'Notifies you the first time a client opens their proposal link',
        badge: 'Helps you strike while the iron is hot',
        icon: EyeIcon,
        iconColor: 'text-blue-600 bg-blue-500/10',
        path: 'notifications.proposals.viewed'
    },
    {
        id: 'revisited',
        title: 'Proposal Revisited',
        description: 'Alerts when a client re-opens a proposal after 48+ hours',
        badge: 'Signals renewed interest or internal deliberation',
        icon: RefreshCwIcon,
        iconColor: 'text-orange-600 bg-orange-500/10',
    path: 'notifications.proposals.revisited'
    },
    {
        id: 'signed',
        title: 'Proposal Signed',
        description: 'High-priority alert when a client digitally signs',
        badge: 'Triggers project setup or invoice creation',
        icon: CheckCircleIcon,
        iconColor: 'text-green-600 bg-green-500/10',
        path: 'notifications.proposals.signed'
    },
    {
        id: 'declined',
        title: 'Proposal Declined',
        description: 'Notifies managers when a client rejects terms',
        badge: 'Includes the custom decline reason',
        icon: XCircleIcon,
        iconColor: 'text-red-600 bg-red-500/10',
        path: 'notifications.proposals.declined'
    }
];

</script>

<template>
    <Head title="Notification settings" />
    <h1 class="sr-only">Notification settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Notifications"
            description="Configure alerts for proposal interactions and team follow-ups"
        />

        <Form
            v-bind="notifications.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <fieldset class="space-y-6">

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <BellIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">
                                Proposal Notifications
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Get notified when clients interact with your proposals
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 pl-10">
                        <div 
                            v-for="toggle in proposalToggles" 
                            :key="toggle.id"
                            class="flex items-center justify-between rounded-lg border border-border p-4"
                        >
                            <div class="flex items-start gap-3">
                                <div :class="['flex h-8 w-8 items-center justify-center rounded-md', toggle.iconColor]">
                                    <component :is="toggle.icon" class="h-4 w-4" />
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-medium text-foreground">
                                        {{ toggle.title }}
                                    </h4>
                                    <p class="text-xs text-muted-foreground">
                                        {{ toggle.description }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ toggle.badge }}
                                    </p>
                                </div>
                            </div>
                            <Switch
                                :id="`proposal_${toggle.id}`"
                                :name="`notification_settings[${toggle.path.replace(/\./g, '][')}]`"
                                v-model="syncToggle(toggle.path).value"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="min-w-32"
                    >
                        <span v-if="processing">Saving...</span>
                        <span v-else>Save Settings</span>
                    </Button>
                </div>
            </fieldset>
        </Form>
    </div>
</template>