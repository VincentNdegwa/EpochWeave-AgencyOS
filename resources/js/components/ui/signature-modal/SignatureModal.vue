<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SignaturePad } from '@/components/ui/signature-pad';
import { XIcon } from '@lucide/vue';
import { Proposal } from '@/types/models/proposal';

const props = defineProps<{
    isOpen: boolean;
    proposal: Proposal;
    requireName?: boolean;
    requireDate?: boolean;
    requireSignature?: boolean;
    title?: string;
    description?: string;
    mode?: 'sign' | 'reject';
}>();

const emit = defineEmits<{
    close: [];
    accept: [payload: { name?: string; date?: string; signature?: string }];
    reject: [reason?: string];
}>();

const signerName = ref('');
const signatureData = ref('');
const rejectReason = ref('');
const currentMode = computed(() => props.mode || 'sign');
const isSubmitting = ref(false);
const error = ref('');

const isValid = computed(() => {
    if (currentMode.value === 'reject') {
        return true;
    }
    
    if (!props.requireName && !props.requireSignature) {
        return true;
    }
    
    if (props.requireName && !signerName.value.trim()) {
        return false;
    }
    
    if (props.requireSignature && !signatureData.value) {
        return false;
    }
    
    return true;
});

const handleSignature = (data: string) => {
    signatureData.value = data;
};

const handleAccept = () => {
    if (!isValid.value) return;
    
    isSubmitting.value = true;
    error.value = '';
    
    const payload: { name?: string; date?: string; signature?: string } = {
        date: new Date().toISOString().split('T')[0],
    };
    
    if (props.requireName) {
        payload.name = signerName.value;
    }
    
    if (props.requireSignature) {
        payload.signature = signatureData.value;
    }
    
    router.post(`/proposals/${props.proposal.id}/accept`, payload, {
        onSuccess: () => {
            emit('close');
        },
        onError: (errors) => {
            error.value = 'Failed to accept proposal. Please try again.';
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const handleReject = () => {
    isSubmitting.value = true;
    error.value = '';
    
    const payload = rejectReason.value ? { reason: rejectReason.value } : {};
    
    router.post(`/proposals/${props.proposal.id}/decline`, payload, {
        onSuccess: () => {
            emit('close');
        },
        onError: (errors) => {
            error.value = 'Failed to decline proposal. Please try again.';
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const handleClose = () => {
    emit('close');
};

</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click="handleClose"
    >
        <div
            class="mx-4 w-full max-w-2xl rounded-lg bg-background shadow-lg"
            @click.stop
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-border p-6">
                <div>
                    <h2 class="text-xl font-semibold text-foreground">
                        {{ currentMode === 'reject' ? 'Decline Proposal' : (title || 'Sign Proposal') }}
                    </h2>
                    <p v-if="description" class="mt-1 text-sm text-muted-foreground">
                        {{ currentMode === 'reject' ? 'Please let us know why you\'re declining this proposal.' : description }}
                    </p>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="handleClose"
                    class="h-8 w-8 p-0"
                >
                    <XIcon class="h-4 w-4" />
                </Button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Error Message -->
                <div v-if="error" class="mb-4 p-3 rounded-md bg-destructive/10 border border-destructive/20">
                    <p class="text-sm text-destructive">{{ error }}</p>
                </div>
                <!-- Sign Mode -->
                <div v-if="currentMode === 'sign'" class="space-y-6">
                    <!-- Name Field -->
                    <div v-if="requireName" class="space-y-2">
                        <Label for="signer-name">Full Name *</Label>
                        <Input
                            id="signer-name"
                            v-model="signerName"
                            placeholder="Enter your full name"
                            class="text-base"
                        />
                    </div>

                    <!-- Signature Field -->
                    <div v-if="requireSignature" class="space-y-2">
                        <Label>Signature *</Label>
                        <SignaturePad @signature="handleSignature" @clear="signatureData = ''" />
                    </div>

                    <!-- Quick Accept (no signature required) -->
                    <div v-if="!requireName && !requireSignature" class="text-center py-8">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-foreground mb-2">Accept Proposal</h3>
                        <p class="text-muted-foreground">Click the button below to accept this proposal.</p>
                    </div>
                </div>

                <!-- Reject Mode -->
                <div v-if="currentMode === 'reject'" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="reject-reason">Reason for Declining (Optional)</Label>
                        <textarea
                            id="reject-reason"
                            v-model="rejectReason"
                            rows="4"
                            placeholder="Please let us know why you're declining this proposal..."
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        />
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex justify-between border-t border-border p-6">
                <Button variant="outline" @click="handleClose" :disabled="isSubmitting">
                    Cancel
                </Button>
                
                <div class="flex gap-2">
                    <Button
                        v-if="currentMode === 'reject'"
                        variant="destructive"
                        @click="handleReject"
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting">Declining...</span>
                        <span v-else>Decline Proposal</span>
                    </Button>
                    
                    <Button
                        v-if="currentMode === 'sign'"
                        @click="handleAccept"
                        :disabled="!isValid || isSubmitting"
                    >
                        <span v-if="isSubmitting">Accepting...</span>
                        <span v-else>{{ requireSignature || requireName ? 'Sign & Accept' : 'Accept Proposal' }}</span>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
