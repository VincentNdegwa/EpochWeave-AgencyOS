<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { Form } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useAccountStatuses } from '@/composables/useEnums';
import { dashboard } from '@/routes';
import { index as accountIndex } from '@/routes/accounts';
import { show as accountShow } from '@/routes/accounts';
import { update as accountUpdate } from '@/routes/accounts';
import type { Account } from '@/types/models/account';

const props = defineProps<{
    account: Account;
}>();

const { values: accountStatuses } = useAccountStatuses();

setLayoutProps({
    title: 'Edit Account',
    description: 'Update account information',
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Accounts',
            href: accountIndex(),
        },
        {
            title: props.account.company_name,
            href: accountShow(props.account.id).url,
        },
        {
            title: 'Edit',
        },
    ],
});
</script>

<template>
    <Head title="Edit Account" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Edit Account</h1>
                <p class="text-muted-foreground">
                    Update account information for
                    {{ props.account.company_name }}.
                </p>
            </div>
        </div>

        <!-- Form -->
        <Form
            v-bind="accountUpdate(props.account.id)"
            v-slot="{ errors, processing }"
            class="max-w-2xl space-y-8"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="company_name">Company Name</Label>
                    <Input
                        id="company_name"
                        name="company_name"
                        required
                        :value="props.account.company_name"
                        placeholder="Acme Corporation"
                    />
                    <InputError :message="errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="website">Website</Label>
                    <Input
                        id="website"
                        name="website"
                        type="url"
                        :value="props.account.website"
                        placeholder="https://example.com"
                    />
                    <InputError :message="errors.website" />
                </div>

                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <Select name="status" :model-value="props.account.status">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="status in accountStatuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.status" />
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <Link :href="accountShow(props.account.id).url">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
                <Button type="submit" :disabled="processing"
                    >Update Account</Button
                >
            </div>
        </Form>
    </div>
</template>
