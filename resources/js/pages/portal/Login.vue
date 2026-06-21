<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { LogIn } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({ layout: null });

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/portal/login');
};
</script>

<template>
    <Head title="Client Portal Login" />

    <div class="flex min-h-screen items-center justify-center bg-muted/50">
        <div
            class="w-full max-w-sm space-y-6 rounded-xl border bg-card p-8 shadow-sm"
        >
            <div class="text-center">
                <h1 class="text-xl font-bold">Client Portal</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Sign in to view your proposals and invoices
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="you@company.com"
                        required
                    />
                    <p
                        v-if="form.errors.email"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="space-y-1">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        required
                    />
                    <p
                        v-if="form.errors.password"
                        class="text-xs text-destructive"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <LogIn class="mr-2 h-4 w-4" />
                    Sign In
                </Button>
            </form>
        </div>
    </div>
</template>
