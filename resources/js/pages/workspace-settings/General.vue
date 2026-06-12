<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import {
    BuildingIcon,
    PaletteIcon,
    EyeIcon,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import WorkspaceGeneralSettingsController from '@/actions/App/Http/Controllers/WorkspaceSettings/GeneralController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { ColorPresets } from '@/components/ui/color-presets';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useTemporaryUploads } from '@/composables/useTemporaryUploads';
import { dashboard } from '@/routes';
import { index } from '@/routes/workspace-settings';
import type { Workspace } from '@/types/models/workspace';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Business settings',
                href: index(),
            },
            {
                title: 'General',
            },
        ],
    },
});

const page = usePage();
const workspace = computed(() => page.props.workspace as Workspace | null);
const canUpdateWorkspaceSettings = computed(
    () => page.props.canUpdateWorkspaceSettings as boolean,
);

const uploads = useTemporaryUploads();

const logoUrl = ref('');
const primaryColor = ref('#0F172A');

const whiteLabel = ref(workspace.value?.white_label ?? false);

watch(
    () => workspace.value?.white_label,
    (value) => {
        whiteLabel.value = value ?? false;
    },
    { immediate: true },
);

watch(
    () => workspace.value?.logo_url,
    (value) => {
        if (!uploads.pending.value?.logo) {
            logoUrl.value = value ?? '';
        }
    },
    { immediate: true },
);

watch(
    () => workspace.value?.primary_color,
    (value) => {
        primaryColor.value = value ?? '#0F172A';
    },
    { immediate: true },
);

const uploadLogo = async (event: Event): Promise<void> => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    const result = await uploads.upload({ logo: file });

    target.value = '';

    if (result.logo) {
        logoUrl.value = result.logo;
    }
};

const removeLogo = async (): Promise<void> => {
    if (uploads.pending.value?.logo?.url === logoUrl.value) {
        await uploads.remove('logo');
    }

    logoUrl.value = '';
};

const handleSuccess = async (): Promise<void> => {
    if (uploads.pending.value?.logo?.url === logoUrl.value) {
        uploads.commit('logo');
    }

    await uploads.cleanup();

    logoUrl.value = workspace.value?.logo_url ?? '';
};
</script>

<template>
    <Head title="Business settings" />

    <h1 class="sr-only">Business settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="General"
            description="Basic workspace information"
        />

        <Form
            v-bind="WorkspaceGeneralSettingsController.update.form()"
            class="space-y-6"
            @success="handleSuccess"
            v-slot="{ errors, processing }"
        >
            <fieldset class="space-y-6" :disabled="!canUpdateWorkspaceSettings">
                <input type="hidden" name="logo_url" :value="logoUrl" />
                <input
                    type="hidden"
                    name="primary_color"
                    :value="primaryColor"
                />

                <!-- Basic Information Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <BuildingIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Basic information</h3>
                            <p class="text-xs text-muted-foreground">Core details about your workspace</p>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                name="name"
                                :default-value="workspace?.name"
                                required
                                placeholder="Workspace name"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                name="description"
                                :default-value="workspace?.description || ''"
                                placeholder="What is this workspace for?"
                            />
                            <InputError :message="errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="currency">Currency</Label>
                            <Input
                                id="currency"
                                name="currency"
                                :default-value="workspace?.currency || ''"
                                placeholder="e.g. USD"
                            />
                            <InputError :message="errors.currency" />
                        </div>
                    </div>
                </div>

                <!-- Branding Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <PaletteIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Branding</h3>
                            <p class="text-xs text-muted-foreground">Customize the visual appearance of your workspace</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <Label>Logo</Label>
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 overflow-hidden rounded-lg border bg-muted">
                                    <img
                                        v-if="logoUrl"
                                        :src="logoUrl"
                                        alt="Workspace logo"
                                        class="h-full w-full object-cover"
                                    />
                                </div>

                                <div class="flex-1 space-y-2">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        :disabled="uploads.isUploading.value"
                                        class="dark:bg-input/30 border-input focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:aria-invalid:border-destructive/50 disabled:bg-input/50 dark:disabled:bg-input/80 h-8 rounded-lg border bg-transparent px-2.5 py-1 text-base transition-colors file:h-6 file:text-sm file:font-medium focus-visible:ring-3 aria-invalid:ring-3 md:text-sm w-full min-w-0 outline-none file:inline-flex file:border-0 file:bg-transparent file:text-foreground placeholder:text-muted-foreground disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50"
                                        @change="uploadLogo"
                                    />
                                    <InputError :message="errors.logo_url" />
                                    <p
                                        v-if="(uploads.errors.value?.length || 0) > 0"
                                        class="text-sm text-destructive"
                                    >
                                        {{ uploads.errors.value?.[0] }}
                                    </p>
                                </div>

                                <Button
                                    v-if="logoUrl"
                                    type="button"
                                    variant="outline"
                                    @click="removeLogo"
                                >
                                    Remove
                                </Button>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Primary color</Label>
                            <ColorPresets v-model="primaryColor" />
                            <InputError :message="errors.primary_color" />
                        </div>
                    </div>
                </div>

                <!-- Privacy Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10">
                            <EyeIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Privacy</h3>
                            <p class="text-xs text-muted-foreground">Control platform visibility and branding</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label for="white_label">White label</Label>
                            <p class="text-sm text-muted-foreground">
                                Hide platform branding in client-facing views.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="hidden"
                                name="white_label"
                                :value="whiteLabel ? '1' : '0'"
                            />
                            <Switch
                                id="white_label"
                                v-model="whiteLabel"
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing || !canUpdateWorkspaceSettings"
                    data-test="update-workspace-settings-button"
                >
                    Save
                </Button>
                <p
                    v-if="!canUpdateWorkspaceSettings"
                    class="text-sm text-muted-foreground"
                >
                    You don’t have permission to edit these settings.
                </p>
            </div>
        </Form>
    </div>
</template>
