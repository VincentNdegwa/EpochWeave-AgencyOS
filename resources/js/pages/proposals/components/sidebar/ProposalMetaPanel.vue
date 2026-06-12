<script setup lang="ts">
import {
  FileTextIcon,
  CoinsIcon,
  CalendarIcon,
  HashIcon,
  BanknoteIcon,
  AlertCircleIcon,
  FileIcon,
} from '@lucide/vue';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
import type { Ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { useBuilderDataStore } from '@/stores/builderData';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { Proposal } from '@/types/models/proposal';

const builderStore = useProposalBuilderStore();
const { proposal } = storeToRefs(builderStore) as { proposal: Ref<Proposal> };
const builderDataStore = useBuilderDataStore();
const { templates, accounts } = storeToRefs(builderDataStore);

const isApplyingTemplate = ref(false);
const selectedTemplateId = ref<string | null>(null);

const { fetchTemplates, fetchAccounts } = builderDataStore;
fetchTemplates();
fetchAccounts();

const proposalTitleModel = computed({
  get: () => proposal.value.title,
  set: (value: string) => {
    proposal.value.title = value?.trim() ? value : 'Untitled proposal';
  },
});

const displayProposalNumber = computed(() => proposal.value.proposal_number ?? proposal.value.token ?? 'DRAFT');

const selectedAccountModel = computed({
  get: () => (proposal.value.account_id ? proposal.value.account_id.toString() : null),
  set: (value: string | null) => {
    proposal.value.account_id = value ? Number(value) : null;
  },
});

const selectedTemplateModel = computed({
  get: () => selectedTemplateId.value,
  set: (value: string | null) => {
    selectedTemplateId.value = value;
  },
});

const currencies = [
  { value: 'KES', label: 'KES', name: 'Kenyan Shilling', flag: '🇰🇪' },
  { value: 'NGN', label: 'NGN', name: 'Nigerian Naira', flag: '🇳🇬' },
  { value: 'GHS', label: 'GHS', name: 'Ghanaian Cedi', flag: '🇬🇭' },
  { value: 'USD', label: 'USD', name: 'US Dollar', flag: '🇺🇸' },
  { value: 'GBP', label: 'GBP', name: 'British Pound', flag: '🇬🇧' },
  { value: 'EUR', label: 'EUR', name: 'Euro', flag: '🇪🇺' },
] as const;

const selectedCurrency = computed(() =>
  currencies.find((currency) => currency.value === proposal.value.currency) ?? currencies[0]
);

const today = new Date().toISOString().split('T')[0];
const isExpired = computed(() => (proposal.value.valid_until ? proposal.value.valid_until < today : false));

const daysUntil = computed(() => {
  if (!proposal.value.valid_until) {
    return null;
  }

  return Math.ceil((new Date(proposal.value.valid_until).getTime() - Date.now()) / 86_400_000);
});

const validityHint = computed(() => {
  if (!proposal.value.valid_until) {
    return null;
  }

  if (isExpired.value) {
    return 'This proposal has expired';
  }

  if (daysUntil.value === 0) {
    return 'Expires today';
  }

  if (daysUntil.value === 1) {
    return 'Expires tomorrow';
  }

  return daysUntil.value !== null ? `Expires in ${daysUntil.value} days` : null;
});

const depositPresets = [10, 25, 50] as const;

const setValidityDays = (days: number) => {
  const future = new Date();
  future.setDate(future.getDate() + days);
  proposal.value.valid_until = future.toISOString().split('T')[0];
};

const clearValidity = () => {
  proposal.value.valid_until = null;
};

const validUntilModel = computed({
  get: () => proposal.value.valid_until ?? '',
  set: (value: string) => {
    proposal.value.valid_until = value || null;
  },
});

const depositValueModel = computed({
  get: () => proposal.value.deposit_value ?? 0,
  set: (value: number | string) => {
    const numericValue = typeof value === 'number' ? value : Number(value);
    proposal.value.deposit_value = Number.isFinite(numericValue) ? numericValue : 0;
  },
});

const toggleDepositRequirement = (value: boolean) => {
  proposal.value.requires_deposit = value;

  if (value && !proposal.value.deposit_type) {
    proposal.value.deposit_type = 'percentage';
    proposal.value.deposit_value = proposal.value.deposit_value ?? 0;
  }

  if (!value) {
    proposal.value.deposit_type = null;
    proposal.value.deposit_value = null;
  }
};

const setDepositType = (type: 'percentage' | 'fixed') => {
  proposal.value.deposit_type = type;

  if (proposal.value.deposit_value == null) {
    proposal.value.deposit_value = 0;
  }
};

const applyTemplate = async () => {
  if (!selectedTemplateId.value || isApplyingTemplate.value) {
return;
}

  isApplyingTemplate.value = true;

  try {
    const templateId = parseInt(selectedTemplateId.value, 10);

    if (Number.isNaN(templateId)) {
      throw new Error('Invalid template ID');
    }

    const template = await builderDataStore.fetchTemplate(templateId);

    if (template.content && Array.isArray(template.content)) {
      builderStore.loadBlocks(template.content);
    }

    proposal.value.template_id = templateId;
    selectedTemplateId.value = null;
  } catch (error) {
    console.error('Failed to apply template:', error);
  } finally {
    isApplyingTemplate.value = false;
  }
};
</script>

<template>
  <div class="flex flex-col gap-0 text-sm">
    <div class="px-4 py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <FileTextIcon class="h-3 w-3" />
        Proposal
      </p>

      <div class="mb-3 grid gap-1.5">
        <Label class="text-xs text-muted-foreground">Title</Label>
        <Input
          v-model="proposalTitleModel"
          placeholder="e.g. Brand Identity Package"
          class="h-8 text-sm font-medium"
        />
      </div>

      <div class="flex items-center justify-between rounded-md bg-muted/50 px-3 py-2">
        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
          <HashIcon class="h-3 w-3" />
          Proposal number
        </div>
        <span class="font-mono text-xs font-semibold text-foreground">
          {{ displayProposalNumber }}
        </span>
      </div>
    </div>

    <div class="px-4 py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <BanknoteIcon class="h-3 w-3" />
        Account
      </p>

      <div class="mb-3">
        <Label class="mb-1.5 block text-xs text-muted-foreground">Select account</Label>
        <Select v-model="selectedAccountModel">
          <SelectTrigger class="w-full">
            <SelectValue placeholder="Choose an account..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="account in accounts"
              :key="account.id"
              :value="account.id.toString()"
            >
              {{ account.company_name ?? account.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <div class="px-4 py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <FileIcon class="h-3 w-3" />
        Template
      </p>

      <div class="mb-3">
        <Label class="mb-1.5 block text-xs text-muted-foreground">Select template</Label>
        <Select
          v-model="selectedTemplateModel"
          :disabled="isApplyingTemplate"
        >
          <SelectTrigger class="w-full">
            <SelectValue placeholder="Choose a template..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="template in templates"
              :key="template.id"
              :value="template.id.toString()"
            >
              {{ template.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <Button
        v-if="selectedTemplateId"
        @click="applyTemplate"
        :disabled="isApplyingTemplate"
        size="sm"
        class="w-full"
      >
        <FileIcon class="h-3 w-3 mr-1.5" />
        {{ isApplyingTemplate ? 'Applying...' : 'Apply Template' }}
      </Button>
    </div>

    <div class="px-4 py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <CoinsIcon class="h-3 w-3" />
        Currency
      </p>

      <div class="mb-2 flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2">
        <span class="text-base leading-none">{{ selectedCurrency.flag }}</span>
        <div class="flex-1">
          <p class="text-xs font-semibold text-foreground">{{ selectedCurrency.label }}</p>
          <p class="text-[11px] text-muted-foreground">{{ selectedCurrency.name }}</p>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-1.5">
        <button
          v-for="currency in currencies"
          :key="currency.value"
          type="button"
          class="flex items-center gap-1.5 rounded-md border px-2 py-1.5 text-xs font-medium transition"
          :class="proposal.currency === currency.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          @click="proposal.currency = currency.value"
        >
          <span class="text-sm leading-none">{{ currency.flag }}</span>
          {{ currency.label }}
        </button>
      </div>
    </div>

    <div class="px-4 py-3 border-b border-border">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <CalendarIcon class="h-3 w-3" />
        Valid until
      </p>

      <Input
        type="date"
        :min="today"
        v-model="validUntilModel"
        class="h-8 text-sm"
      />

      <div
        v-if="validityHint"
        class="mt-2 flex items-center gap-1.5 text-[11px]"
        :class="isExpired
          ? 'text-destructive'
          : daysUntil !== null && daysUntil <= 3
            ? 'text-amber-600 dark:text-amber-400'
            : 'text-muted-foreground'"
      >
        <AlertCircleIcon v-if="isExpired || (daysUntil !== null && daysUntil <= 3)" class="h-3 w-3 flex-shrink-0" />
        {{ validityHint }}
      </div>

      <div class="mt-2.5 flex gap-1.5">
        <button
          v-for="days in [7, 14, 30]"
          :key="days"
          type="button"
          class="flex-1 rounded-md border border-border bg-background py-1 text-center text-[11px] text-muted-foreground transition hover:border-primary hover:text-primary"
          @click="setValidityDays(days)"
        >
          +{{ days }}d
        </button>
        <button
          v-if="proposal.valid_until"
          type="button"
          class="rounded-md border border-border bg-background px-2 py-1 text-[11px] text-muted-foreground transition hover:border-destructive hover:text-destructive"
          @click="clearValidity"
        >
          Clear
        </button>
      </div>
    </div>

    <div class="px-4 py-3">
      <p class="mb-3 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
        <BanknoteIcon class="h-3 w-3" />
        Deposit
      </p>

      <label
        class="flex cursor-pointer items-center justify-between rounded-lg border px-3 py-2.5 transition-colors"
        :class="proposal.requires_deposit
          ? 'border-primary bg-primary/5'
          : 'border-border hover:bg-muted/50'"
      >
        <div>
          <p class="text-xs font-medium text-foreground">Require a deposit</p>
          <p class="text-[11px] text-muted-foreground">Client pays before work begins</p>
        </div>
        <Switch
          :model-value="proposal.requires_deposit"
          @update:model-value="toggleDepositRequirement"
        />
      </label>

      <div v-if="proposal.requires_deposit" class="mt-3 space-y-3">
        <div>
          <Label class="mb-1.5 block text-xs text-muted-foreground">Deposit type</Label>
          <div class="grid grid-cols-2 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
            <button
              type="button"
              class="rounded py-1.5 text-xs font-medium transition"
              :class="proposal.deposit_type === 'percentage'
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
              @click="setDepositType('percentage')"
            >
              Percentage %
            </button>
            <button
              type="button"
              class="rounded py-1.5 text-xs font-medium transition"
              :class="proposal.deposit_type === 'fixed'
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
              @click="setDepositType('fixed')"
            >
              Fixed {{ selectedCurrency.label }}
            </button>
          </div>
        </div>

        <div>
          <Label class="mb-1.5 block text-xs text-muted-foreground">
            {{ proposal.deposit_type === 'percentage' ? 'Percentage' : 'Amount' }}
          </Label>

          <template v-if="proposal.deposit_type === 'percentage'">
            <div class="mb-2 grid grid-cols-4 gap-1">
              <button
                v-for="preset in depositPresets"
                :key="preset"
                type="button"
                class="rounded-md border py-1.5 text-xs font-medium transition"
                :class="proposal.deposit_value === preset
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
                @click="proposal.deposit_value = preset"
              >
                {{ preset }}%
              </button>
              <button
                type="button"
                class="rounded-md border py-1.5 text-xs font-medium text-muted-foreground"
                :class="!depositPresets.includes(proposal.deposit_value as typeof depositPresets[number])
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border bg-background hover:border-muted-foreground hover:text-foreground'"
              >
                Custom
              </button>
            </div>
            <div class="relative">
              <Input
                type="number"
                min="1"
                max="100"
                v-model.number="depositValueModel"
                class="h-8 pr-8 text-sm"
              />
              <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">
                %
              </span>
            </div>
          </template>

          <template v-else>
            <div class="relative">
              <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-medium text-muted-foreground">
                {{ selectedCurrency.label }}
              </span>
              <Input
                type="number"
                min="0"
                v-model.number="depositValueModel"
                class="h-8 pl-12 text-sm"
              />
            </div>
          </template>
        </div>

        <div class="flex items-center gap-2 rounded-md bg-muted/50 px-3 py-2">
          <BanknoteIcon class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground" />
          <p class="text-[11px] text-muted-foreground">
            Client pays
            <span class="font-semibold text-foreground">
              <template v-if="proposal.deposit_type === 'percentage'">
                {{ proposal.deposit_value }}%
              </template>
              <template v-else>
                {{ selectedCurrency.label }} {{ (proposal.deposit_value ?? 0).toLocaleString() }}
              </template>
            </span>
            upfront before work begins.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>