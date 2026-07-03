<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import engagementStore from '@/actions/App/Http/Controllers/EngagementController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import {
    useEngagementDirections,
    useEngagementOutcomes,
    useEngagementStatuses,
    useEngagementTypes,
} from '@/composables/useEnums';
import { useBuilderDataStore } from '@/stores/builderData';
import type { Account, Engagement } from '@/types/models/account';

interface Props {
    open: boolean;
    account: Account;
    engagement?: Engagement | null;
}

const props = withDefaults(defineProps<Props>(), {
    engagement: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const builderData = useBuilderDataStore();
const { projects, proposals, invoices } = storeToRefs(builderData);

const isEditing = computed(() => props.engagement !== null);

const accountProjects = computed(() =>
    (projects.value || []).filter((p) => p.account_id === props.account.id),
);
const accountProposals = computed(() =>
    (proposals.value || []).filter((p) => p.account_id === props.account.id),
);
const accountInvoices = computed(() =>
    (invoices.value || []).filter((i) => i.account_id === props.account.id),
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            builderData.fetchProjects({
                account_id: props.account.id.toString(),
            });
            builderData.fetchProposals({
                account_id: props.account.id.toString(),
            });
            builderData.fetchInvoices({
                account_id: props.account.id.toString(),
            });
        }
    },
);

const engagementTypes = useEngagementTypes();
const engagementDirections = useEngagementDirections();
const engagementStatuses = useEngagementStatuses();
const engagementOutcomes = useEngagementOutcomes();

const toDateTimeLocal = (value?: string | null): string => {
    if (!value) {
return '';
}

    return new Date(value).toISOString().slice(0, 16);
};

const form = ref({
    account_id: props.account.id.toString(),
    type: 'call',
    direction: 'outbound',
    status: 'completed',
    subject: '',
    content: '',
    proposal_id: 'none',
    invoice_id: 'none',
    project_id: 'none',
    scheduled_at: '',
    completed_at: '',
    follow_up_at: '',
    outcome: 'none',
});

watch(
    () => [props.open, props.engagement],
    () => {
        if (!props.open) {
            return;
        }

        const engagement = props.engagement;

        if (engagement) {
            form.value = {
                account_id: engagement.account_id?.toString() ?? props.account.id.toString(),
                type: engagement.type,
                direction: engagement.direction,
                status: engagement.status,
                subject: engagement.subject ?? '',
                content: engagement.content ?? '',
                proposal_id: engagement.proposal?.id?.toString() ?? 'none',
                invoice_id: engagement.invoice?.id?.toString() ?? 'none',
                project_id: engagement.project?.id?.toString() ?? 'none',
                scheduled_at: toDateTimeLocal(engagement.scheduled_at),
                completed_at: toDateTimeLocal(engagement.completed_at),
                follow_up_at: toDateTimeLocal(engagement.follow_up_at),
                outcome: engagement.outcome ?? 'none',
            };
        } else {
            form.value = {
                account_id: props.account.id.toString(),
                type: 'call',
                direction: 'outbound',
                status: 'completed',
                subject: '',
                content: '',
                proposal_id: 'none',
                invoice_id: 'none',
                project_id: 'none',
                scheduled_at: '',
                completed_at: new Date().toISOString().slice(0, 16),
                follow_up_at: '',
                outcome: 'none',
            };
        }
    },
    { immediate: true },
);

const formAction = computed(() => {
    if (props.engagement) {
        return engagementStore.update.form(props.engagement) as any;
    }

    return engagementStore.store.form() as any;
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-h-[90vh] overflow-hidden sm:max-w-3xl">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Edit Engagement' : 'Log Engagement' }}
                </DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Update the engagement details.' : 'Record a touchpoint with ' + account.company_name + '.' }}
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="formAction"
                :options="{ preserveScroll: true, preserveState: true }"
                @success="
                    emit('success');
                    emit('update:open', false);
                "
                v-slot="{ errors, processing }"
            >
                <input
                    type="hidden"
                    name="account_id"
                    :value="form.account_id"
                />

                <div
                    class="grid max-h-[calc(90vh-180px)] gap-6 overflow-y-auto py-4 pr-2"
                >
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="engagement-type" required>Type</Label>
                            <Select
                                name="type"
                                :model-value="form.type"
                                @update:model-value="
                                    form.type = $event as string
                                "
                            >
                                <SelectTrigger
                                    id="engagement-type"
                                    class="w-full"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opt in engagementTypes.values"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-direction" required
                                >Direction</Label
                            >
                            <Select
                                name="direction"
                                :model-value="form.direction"
                                @update:model-value="
                                    form.direction = $event as string
                                "
                            >
                                <SelectTrigger
                                    id="engagement-direction"
                                    class="w-full"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opt in engagementDirections.values"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.direction" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-status" required
                                >Status</Label
                            >
                            <Select
                                name="status"
                                :model-value="form.status"
                                @update:model-value="
                                    form.status = $event as string
                                "
                            >
                                <SelectTrigger
                                    id="engagement-status"
                                    class="w-full"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opt in engagementStatuses.values"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.status" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="engagement-subject">Subject</Label>
                        <Input
                            id="engagement-subject"
                            name="subject"
                            v-model="form.subject"
                            placeholder="e.g. Cold outreach - website redesign"
                        />
                        <InputError :message="errors.subject" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="engagement-content">Content</Label>
                        <Textarea
                            id="engagement-content"
                            name="content"
                            v-model="form.content"
                            placeholder="What was discussed or sent..."
                            rows="4"
                        />
                        <InputError :message="errors.content" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="engagement-scheduled"
                                >Scheduled At</Label
                            >
                            <Input
                                id="engagement-scheduled"
                                name="scheduled_at"
                                type="datetime-local"
                                v-model="form.scheduled_at"
                            />
                            <InputError :message="errors.scheduled_at" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-completed"
                                >Completed At</Label
                            >
                            <Input
                                id="engagement-completed"
                                name="completed_at"
                                type="datetime-local"
                                v-model="form.completed_at"
                            />
                            <InputError :message="errors.completed_at" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-follow-up"
                                >Follow Up At</Label
                            >
                            <Input
                                id="engagement-follow-up"
                                name="follow_up_at"
                                type="datetime-local"
                                v-model="form.follow_up_at"
                            />
                            <InputError :message="errors.follow_up_at" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="engagement-proposal"
                                >Related Proposal</Label
                            >
                            <Select
                                name="proposal_id"
                                :model-value="form.proposal_id"
                                @update:model-value="
                                    form.proposal_id = $event as string
                                "
                            >
                                <SelectTrigger
                                    id="engagement-proposal"
                                    class="w-full"
                                >
                                    <SelectValue placeholder="None" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">None</SelectItem>
                                    <SelectItem
                                        v-for="p in accountProposals"
                                        :key="p.id"
                                        :value="p.id.toString()"
                                    >
                                        {{ p.proposal_number }} - {{ p.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.proposal_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-invoice"
                                >Related Invoice</Label
                            >
                            <Select
                                name="invoice_id"
                                :model-value="form.invoice_id"
                                @update:model-value="
                                    form.invoice_id = $event as string
                                "
                            >
                                <SelectTrigger
                                    id="engagement-invoice"
                                    class="w-full"
                                >
                                    <SelectValue placeholder="None" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">None</SelectItem>
                                    <SelectItem
                                        v-for="i in accountInvoices"
                                        :key="i.id"
                                        :value="i.id.toString()"
                                    >
                                        {{ i.invoice_number }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.invoice_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-project"
                                >Related Project</Label
                            >
                            <Select
                                name="project_id"
                                :model-value="form.project_id"
                                @update:model-value="
                                    form.project_id = $event as string
                                "
                            >
                                <SelectTrigger
                                    id="engagement-project"
                                    class="w-full"
                                >
                                    <SelectValue placeholder="None" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">None</SelectItem>
                                    <SelectItem
                                        v-for="p in accountProjects"
                                        :key="p.id"
                                        :value="p.id.toString()"
                                    >
                                        {{ p.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.project_id" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="engagement-outcome">Outcome</Label>
                            <Select
                                name="outcome"
                                :model-value="form.outcome"
                                @update:model-value="
                                    form.outcome = $event as string
                                "
                            >
                                <SelectTrigger
                                    id="engagement-outcome"
                                    class="w-full"
                                >
                                    <SelectValue placeholder="Select outcome" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">None</SelectItem>
                                    <SelectItem
                                        v-for="opt in engagementOutcomes.values"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.outcome" />
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ isEditing ? 'Update Engagement' : 'Log Engagement' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
