<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import PortalSetupController from '@/actions/App/Http/Controllers/PortalSetupController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineProps<{
    token: string;
    email: string;
    company_name: string;
}>();

defineOptions({
    layout: {
        title: 'Complete Portal Setup',
        description: 'Create your password to access the client portal',
    },
});
</script>

<template>
    <Head title="Complete Portal Setup" />

    <div class="flex flex-col gap-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold">Welcome to {{ company_name }}</h1>
            <p class="text-muted-foreground">
                Complete your portal setup to get started
            </p>
        </div>

        <Form
            v-bind="PortalSetupController.complete(token)"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        :value="email"
                        disabled
                        class="bg-muted"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Create password</Label>
                    <PasswordInput
                        id="password"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Create a strong password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="2"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm your password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="3"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Complete Setup
                </Button>
            </div>
        </Form>

        <div
            v-if="$page.props.errors.error"
            class="text-center text-sm text-destructive"
        >
            {{ $page.props.errors.error }}
        </div>
    </div>
</template>
