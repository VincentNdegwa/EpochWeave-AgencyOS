<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import EngagementController from '@/actions/App/Http/Controllers/EngagementController';
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
import { useBuilderDataStore } from '@/stores/builderData';
import type { Account } from '@/types/models/account';
import {
    useEngagementDirections,
    useEngagementOutcomes,
    useEngagementStatuses,
    useEngagementTypes,
} from '@/composables/useEnums';

interface Props {
    open: boolean;
    account: Account;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const builderData = useBuilderDataStore();
const { projects, projectsLoaded, proposals, proposalsLoaded, invoices, invoicesLoaded } =
    storeToRefs(builderData);

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
            builderData.fetchProjects({ account_id: props.account.id.toString() });
            builderData.fetchProposals({ account_id: props.account.id.toString() });
            builderData.fetchInvoices({ account_id: props.account.id.toString() });
        }
    },
);

const engagementTypes = useEngagementTypes();
const engagementDirections = useEngagementDirections();
const engagementStatuses = useEngagementStatuses();
const engagementOutcomes = useEngagementOutcomes();

const form = ref({
    account_id: props.account.id.toString(),
    type: 'call',
    direction: 'outbound',
    status: 'completed',
    subject: '',
    content: '',
    proposal_id: '',
    invoice_id: '',
    project_id: '',
    scheduled_at: '',
    completed_at: '',
    follow_up_at: '',
    outcome: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.value = {
                account_id: props.account.id.toString(),
                type: 'call',
                direction: 'outbound',
                status: 'completed',
                subject: '',
                content: '',
                proposal_id: '',
                invoice_id: '',
                project_id: '',
                scheduled_at: '',
                completed_at: new Date().toISOString().slice(0, 16),
                follow_up_at: '',
                outcome: '',
            };
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Log Engagement</DialogTitle>
                <DialogDescription>
                    Record a touchpoint with {{ account.company_name }}.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="EngagementController.store.form() as any"
                :options="{ preserveScroll: true, preserveState: true }"
                @success="
                    emit('success');
                    emit('update:open', false);
                "
                v-slot="{ errors, processing }"
            >
                <input type="hidden" name="account_id" :value="form.account_id" />

                <div class="grid gap-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="engagement-type" required>Type</Label>
                            <Select
                                name="type"
                                :model-value="form.type"
                                @update:model-value="form.type = $event"
                            >
                                <SelectTrigger id="engagement-type">
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
                            <Label for="engagement-direction" required>Direction</Label>
                            <Select
                                name="direction"
                                :model-value="form.direction"
                                @update:model-value="form.direction = $event"
                            >
                                <SelectTrigger id="engagement-direction">
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
                            <Label for="engagement-status" required>Status</Label>
                            <Select
                                name="status"
                                :model-value="form.status"
                                @update:model-value="form.status = $event"
                            >
                                <SelectTrigger id="engagement-status">
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
                            <Label for="engagement-proposal">Related Proposal</Label>
                            <Select
                                name="proposal_id"
                                :model-value="form.proposal_id"
                                @update:model-value="form.proposal_id = $event"
                            >
                                <SelectTrigger id="engagement-proposal">
                                    <SelectValue placeholder="None" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">None</SelectItem>
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
                            <Label for="engagement-invoice">Related Invoice</Label>
                            <Select
                                name="invoice_id"
                                :model-value="form.invoice_id"
                                @update:model-value="form.invoice_id = $event"
                            >
                                <SelectTrigger id="engagement-invoice">
                                    <SelectValue placeholder="None" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">None</SelectItem>
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
                            <Label for="engagement-project">Related Project</Label>
                            <Select
                                name="project_id"
                                :model-value="form.project_id"
                                @update:model-value="form.project_id = $event"
                            >
                                <SelectTrigger id="engagement-project">
                                    <SelectValue placeholder="None" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">None</SelectItem>
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

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="engagement-scheduled">Scheduled At</Label>
                            <Input
                                id="engagement-scheduled"
                                name="scheduled_at"
                                type="datetime-local"
                                v-model="form.scheduled_at"
                            />
                            <InputError :message="errors.scheduled_at" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-completed">Completed At</Label>
                            <Input
                                id="engagement-completed"
                                name="completed_at"
                                type="datetime-local"
                                v-model="form.completed_at"
                            />
                            <InputError :message="errors.completed_at" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="engagement-follow-up">Follow Up At</Label>
                            <Input
                                id="engagement-follow-up"
                                name="follow_up_at"
                                type="datetime-local"
                                v-model="form.follow_up_at"
                            />
                            <InputError :message="errors.follow_up_at" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="engagement-outcome">Outcome</Label>
                            <Select
                                name="outcome"
                                :model-value="form.outcome"
                                @update:model-value="form.outcome = $event"
                            >
                                <SelectTrigger id="engagement-outcome">
                                    <SelectValue placeholder="Select outcome" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">None</SelectItem>
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
                        Log Engagement
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
