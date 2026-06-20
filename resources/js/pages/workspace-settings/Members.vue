<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import { Trash2, UserPlus, Users } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Workspace Members' },
        ],
    },
});

const props = defineProps<{
    members: {
        id: number;
        name: string;
        email: string;
        created_at: string;
        roles: { id: number; name: string; display_name: string | null }[];
    }[];
    roles: { id: number; name: string; display_name: string | null }[];
}>();

const inviteForm = useForm({
    email: '',
    role_id: props.roles[0]?.id ?? '',
});

const showInvite = ref(false);

const submitInvite = () => {
    inviteForm.post('/workspace/members', {
        onFinish: () => {
            showInvite.value = false;
            inviteForm.reset();
        },
    });
};

const removeMember = (userId: number) => {
    if (!confirm('Remove this member from the workspace?')) return;

    const form = useForm({});
    form.delete(`/workspace/members/${userId}`);
};
</script>

<template>
    <Head title="Workspace Members" />

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Users class="h-5 w-5 text-muted-foreground" />
                <h1 class="text-lg font-semibold">Workspace Members</h1>
                <span class="text-sm text-muted-foreground">({{ members.length }})</span>
            </div>
            <Button size="sm" class="gap-1" @click="showInvite = true">
                <UserPlus class="h-4 w-4" />
                Invite
            </Button>
        </div>

        <div v-if="showInvite" class="rounded-lg border p-4">
            <form class="grid gap-3" @submit.prevent="submitInvite">
                <div>
                    <Label class="text-xs">Email</Label>
                    <Input v-model="inviteForm.email" type="email" placeholder="colleague@company.com" required />
                    <p v-if="inviteForm.errors.email" class="text-xs text-destructive">{{ inviteForm.errors.email }}</p>
                </div>
                <div>
                    <Label class="text-xs">Role</Label>
                    <select v-model="inviteForm.role_id" class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                        <option v-for="role in roles" :key="role.id" :value="role.id">
                            {{ role.display_name ?? role.name }}
                        </option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" variant="ghost" size="sm" @click="showInvite = false">Cancel</Button>
                    <Button type="submit" size="sm" :disabled="inviteForm.processing">Send Invite</Button>
                </div>
            </form>
        </div>

        <div class="space-y-2">
            <div
                v-for="member in members"
                :key="member.id"
                class="flex items-center justify-between rounded-lg border p-4"
            >
                <div>
                    <p class="text-sm font-medium">{{ member.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ member.email }}</p>
                    <p class="text-xs text-muted-foreground">
                        <span v-for="role in member.roles" :key="role.id" class="mr-1 rounded bg-muted px-1.5 py-0.5 text-[10px] uppercase tracking-wider">
                            {{ role.display_name ?? role.name }}
                        </span>
                    </p>
                </div>
                <Button
                    size="icon"
                    variant="ghost"
                    class="h-8 w-8 text-muted-foreground hover:text-destructive"
                    @click="removeMember(member.id)"
                >
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>

            <p v-if="!members.length" class="text-sm text-muted-foreground">No members in this workspace yet.</p>
        </div>
    </div>
</template>
