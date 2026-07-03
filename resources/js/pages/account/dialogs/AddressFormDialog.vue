<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import type { Address } from '@/types/models/account';

interface Props {
    open: boolean;
    addressableType: string;
    addressableId: number;
    address?: Address | null;
}

const props = withDefaults(defineProps<Props>(), {
    address: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const isEditMode = computed(() => !!props.address);

const type = ref(props.address?.type ?? 'primary');
const label = ref(props.address?.label ?? '');
const street1 = ref(props.address?.street_1 ?? '');
const street2 = ref(props.address?.street_2 ?? '');
const city = ref(props.address?.city ?? '');
const state = ref(props.address?.state ?? '');
const postalCode = ref(props.address?.postal_code ?? '');
const country = ref(props.address?.country ?? '');
const isPrimary = ref(props.address?.is_primary ?? false);

const formAction = computed(() => {
    if (isEditMode.value && props.address) {
        return `/addresses/${props.address.id}`;
    }

    return '/addresses';
});

const formMethod = computed(() => {
    return isEditMode.value ? 'patch' : 'post';
});

watch(
    () => props.address,
    (newAddress) => {
        if (newAddress) {
            type.value = newAddress.type;
            label.value = newAddress.label ?? '';
            street1.value = newAddress.street_1;
            street2.value = newAddress.street_2 ?? '';
            city.value = newAddress.city;
            state.value = newAddress.state ?? '';
            postalCode.value = newAddress.postal_code ?? '';
            country.value = newAddress.country;
            isPrimary.value = newAddress.is_primary;
        } else {
            type.value = 'primary';
            label.value = '';
            street1.value = '';
            street2.value = '';
            city.value = '';
            state.value = '';
            postalCode.value = '';
            country.value = '';
            isPrimary.value = false;
        }
    },
    { immediate: true },
);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditMode ? 'Edit Address' : 'Add Address' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        isEditMode
                            ? 'Update the address details.'
                            : 'Add a new address.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Form
                :action="formAction"
                :method="formMethod"
                :options="{ preserveScroll: true, preserveState: true }"
                @success="
                    emit('success');
                    emit('update:open', false);
                "
                v-slot="{ errors, processing }"
            >
                <template v-if="!isEditMode">
                    <input
                        type="hidden"
                        name="addressable_type"
                        :value="addressableType"
                    />
                    <input
                        type="hidden"
                        name="addressable_id"
                        :value="addressableId"
                    />
                </template>

                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="address-type" required>Type</Label>
                        <Select name="type" v-model="type">
                            <SelectTrigger id="address-type" class="w-full">
                                <SelectValue placeholder="Select type..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="primary">Primary</SelectItem>
                                <SelectItem value="billing">Billing</SelectItem>
                                <SelectItem value="shipping"
                                    >Shipping</SelectItem
                                >
                                <SelectItem value="office">Office</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address-label">Label</Label>
                        <Input
                            id="address-label"
                            name="label"
                            v-model="label"
                            placeholder="e.g. Headquarters"
                        />
                        <InputError :message="errors.label" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address-street_1" required
                            >Street Address</Label
                        >
                        <Input
                            id="address-street_1"
                            name="street_1"
                            v-model="street1"
                            placeholder="123 Main St"
                            required
                        />
                        <InputError :message="errors.street_1" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address-street_2"
                            >Apartment, suite, etc.</Label
                        >
                        <Input
                            id="address-street_2"
                            name="street_2"
                            v-model="street2"
                            placeholder="Suite 100"
                        />
                        <InputError :message="errors.street_2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="address-city" required>City</Label>
                            <Input
                                id="address-city"
                                name="city"
                                v-model="city"
                                placeholder="New York"
                                required
                            />
                            <InputError :message="errors.city" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="address-state">State / Province</Label>
                            <Input
                                id="address-state"
                                name="state"
                                v-model="state"
                                placeholder="NY"
                            />
                            <InputError :message="errors.state" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="address-postal_code">Postal Code</Label>
                            <Input
                                id="address-postal_code"
                                name="postal_code"
                                v-model="postalCode"
                                placeholder="10001"
                            />
                            <InputError :message="errors.postal_code" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="address-country" required
                                >Country</Label
                            >
                            <Input
                                id="address-country"
                                name="country"
                                v-model="country"
                                placeholder="United States"
                                required
                            />
                            <InputError :message="errors.country" />
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 rounded-lg border border-border bg-background px-3 py-2"
                    >
                        <Label for="address-is_primary" class="cursor-pointer">
                            Primary Address
                        </Label>
                        <div class="flex items-center gap-3">
                            <Checkbox
                                id="address-is_primary"
                                v-model="isPrimary"
                            />
                            <input
                                type="hidden"
                                name="is_primary"
                                :value="isPrimary ? '1' : '0'"
                            />
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
                        {{ isEditMode ? 'Update Address' : 'Add Address' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
