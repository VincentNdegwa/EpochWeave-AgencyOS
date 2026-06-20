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
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { dashboard } from '@/routes';
import { index } from '@/routes/workspace-settings';
import automation from '@/routes/workspace-settings/automation';
import type { AutomationSettings } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Business settings', href: index() },
            { title: 'Automation' },
        ],
    },
});

const page = usePage();

const automationSettings = computed(
    () => page.props.automationSettings as AutomationSettings | null,
);

const syncToggle = (path: string) => computed({
    get: () => {
        if (!automationSettings.value?.settings) {
return false;
}
        
        return path.split('.').reduce((acc: any, key) => acc?.[key], automationSettings.value.settings) ?? false;
    },
    set: (value: boolean) => {
        if (!automationSettings.value?.settings) {
return;
}

        const keys = path.split('.');
        let current = automationSettings.value.settings as any;

        for (let i = 0; i < keys.length - 1; i++) {
            const key = keys[i];

            if (!current[key]) {
current[key] = {};
}

            current = current[key];
        }

        current[keys[keys.length - 1]] = value;
    }
});

const automationToggles = [
    {
        id: 'auto_invoice',
        title: 'Auto-Generate Invoice',
        description: 'Automatically draft and link a structural invoice when a proposal is signed',
        badge: 'Turn off if you manage billing through an external provider',
        icon: MailIcon,
        iconColor: 'text-purple-600 bg-purple-500/10',
        path: 'automation.proposals.auto_generate_invoice'
    },
    {
        id: 'auto_project',
        title: 'Auto-Create Project',
        description: 'Instantly provision a new workspace project board upon client sign-off',
        badge: 'Turn off if you prefer to launch fulfillment tracks manually',
        icon: SparklesIcon,
        iconColor: 'text-indigo-600 bg-indigo-500/10',
        path: 'automation.proposals.auto_create_project'
    }
];

</script>

<template>
    <Head title="Automation settings" />
    <h1 class="sr-only">Automation settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Automation"
            description="Configure workflow automations for proposal acceptance and fulfillment"
        />

        <Form
            v-bind="automation.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <fieldset class="space-y-6">

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <SparklesIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">
                                Workflow Automation
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Automate tasks when proposals are signed
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 pl-10">
                        <div 
                            v-for="toggle in automationToggles" 
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
                                :id="`automation_${toggle.id}`"
                                :name="`automation_settings[${toggle.path.replace(/\./g, '][')}]`"
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
