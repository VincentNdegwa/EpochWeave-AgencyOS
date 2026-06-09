<script setup lang="ts">
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
  FileTextIcon,
  CoinsIcon,
  CalendarIcon,
  HashIcon,
  BanknoteIcon,
  AlertCircleIcon,
  FileIcon,
} from '@lucide/vue';
import type { ProposalMeta } from '@/types/proposal-meta';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { useBuilderDataStore } from '@/stores/builderData';

const builderStore = useProposalBuilderStore();
const { proposalTitle, proposalMeta, selectedTemplateId, selectedAccountId, proposalNumber } = storeToRefs(builderStore);
const builderDataStore = useBuilderDataStore();
const { templates, accounts } = storeToRefs(builderDataStore);

const isApplyingTemplate = ref(false);

// Load templates and accounts when component mounts
const { fetchTemplates, fetchAccounts } = builderDataStore;
fetchTemplates();
fetchAccounts();

const applyTemplate = async () => {
  if (!selectedTemplateId.value || isApplyingTemplate.value) return;
  
  isApplyingTemplate.value = true;
  try {
    const templateId = parseInt(selectedTemplateId.value, 10);
    if (isNaN(templateId)) {
      throw new Error('Invalid template ID');
    }
    
    const template = await builderDataStore.fetchTemplate(templateId);
    
    // Apply template content to builder
    if (template.content && Array.isArray(template.content)) {
      builderStore.loadBlocks(template.content);
    }
    
    // Set template_id in the proposal builder store
    builderStore.setTemplateId(templateId);
    
    // Reset selection
    builderStore.setSelectedTemplateId(null);
  } catch (error) {
    console.error('Failed to apply template:', error);
  } finally {
    isApplyingTemplate.value = false;
  }
};

const currencies = [
  { value: 'KES', label: 'KES', name: 'Kenyan Shilling', flag: '🇰🇪' },
  { value: 'NGN', label: 'NGN', name: 'Nigerian Naira', flag: '🇳🇬' },
  { value: 'GHS', label: 'GHS', name: 'Ghanaian Cedi', flag: '🇬🇭' },
  { value: 'USD', label: 'USD', name: 'US Dollar', flag: '🇺🇸' },
  { value: 'GBP', label: 'GBP', name: 'British Pound', flag: '🇬🇧' },
  { value: 'EUR', label: 'EUR', name: 'Euro', flag: '🇪🇺' },
] as const;

const selectedCurrency = computed(() =>
  currencies.find((currency) => currency.value === proposalMeta.value.currency) ?? currencies[0]
);

const today = new Date().toISOString().split('T')[0];
const isExpired = computed(() =>
  proposalMeta.value.validUntil ? proposalMeta.value.validUntil < today : false
);

const daysUntil = computed(() => {
  if (!proposalMeta.value.validUntil) {
    return null;
  }
  return Math.ceil(
    (new Date(proposalMeta.value.validUntil).getTime() - Date.now()) / 86_400_000
  );
});

const validityHint = computed(() => {
  if (!proposalMeta.value.validUntil) {
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

const updateMeta = (changes: Partial<ProposalMeta>) => {
  builderStore.updateProposalMeta(changes);
};

const setValidityDays = (days: number) => {
  const future = new Date();
  future.setDate(future.getDate() + days);
  updateMeta({ validUntil: future.toISOString().split('T')[0] });
};

const clearValidity = () => updateMeta({ validUntil: null });

const titleModel = computed({
  get: () => proposalTitle.value,
  set: (value: string) => builderStore.setProposalTitle(value),
});

const validUntilModel = computed({
  get: () => proposalMeta.value.validUntil ?? '',
  set: (value: string) => updateMeta({ validUntil: value || null }),
});

const depositValueModel = computed({
  get: () => proposalMeta.value.depositValue,
  set: (value: number | string) => {
    const numericValue = typeof value === 'number' ? value : Number(value);
    updateMeta({ depositValue: Number.isFinite(numericValue) ? numericValue : 0 });
  },
});

const selectedTemplateModel = computed({
  get: () => builderStore.templateId?.toString() ?? null,
  set: (value: string | null) => {
    builderStore.setSelectedTemplateId(value);
  },
});

const selectedAccountModel = computed({
  get: () => selectedAccountId.value,
  set: (value: string | null) => {
    builderStore.setSelectedAccountId(value);
  },
});
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
          v-model="titleModel"
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
          {{ proposalNumber ?? 'DRAFT' }}
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
          :class="proposalMeta.currency === currency.value
            ? 'border-primary bg-primary/5 text-primary'
            : 'border-border bg-background text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
          @click="updateMeta({ currency: currency.value })"
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
          v-if="proposalMeta.validUntil"
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
        :class="proposalMeta.depositEnabled
          ? 'border-primary bg-primary/5'
          : 'border-border hover:bg-muted/50'"
      >
        <div>
          <p class="text-xs font-medium text-foreground">Require a deposit</p>
          <p class="text-[11px] text-muted-foreground">Client pays before work begins</p>
        </div>
        <Switch
          :model-value="proposalMeta.depositEnabled"
          @update:model-value="(value: boolean) => updateMeta({ depositEnabled: value })"
        />
      </label>

      <div v-if="proposalMeta.depositEnabled" class="mt-3 space-y-3">
        <div>
          <Label class="mb-1.5 block text-xs text-muted-foreground">Deposit type</Label>
          <div class="grid grid-cols-2 gap-1 rounded-md border border-border bg-muted/40 p-0.5">
            <button
              type="button"
              class="rounded py-1.5 text-xs font-medium transition"
              :class="proposalMeta.depositType === 'percentage'
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
              @click="updateMeta({ depositType: 'percentage' })"
            >
              Percentage %
            </button>
            <button
              type="button"
              class="rounded py-1.5 text-xs font-medium transition"
              :class="proposalMeta.depositType === 'fixed'
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
              @click="updateMeta({ depositType: 'fixed' })"
            >
              Fixed {{ selectedCurrency.label }}
            </button>
          </div>
        </div>

        <div>
          <Label class="mb-1.5 block text-xs text-muted-foreground">
            {{ proposalMeta.depositType === 'percentage' ? 'Percentage' : 'Amount' }}
          </Label>

          <template v-if="proposalMeta.depositType === 'percentage'">
            <div class="mb-2 grid grid-cols-4 gap-1">
              <button
                v-for="preset in depositPresets"
                :key="preset"
                type="button"
                class="rounded-md border py-1.5 text-xs font-medium transition"
                :class="proposalMeta.depositValue === preset
                  ? 'border-primary bg-primary/5 text-primary'
                  : 'border-border bg-background text-muted-foreground hover:border-muted-foreground'"
                @click="updateMeta({ depositValue: preset })"
              >
                {{ preset }}%
              </button>
              <button
                type="button"
                class="rounded-md border py-1.5 text-xs font-medium text-muted-foreground"
                :class="!depositPresets.includes(proposalMeta.depositValue as typeof depositPresets[number])
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
              <template v-if="proposalMeta.depositType === 'percentage'">
                {{ proposalMeta.depositValue }}%
              </template>
              <template v-else>
                {{ selectedCurrency.label }} {{ (proposalMeta.depositValue ?? 0).toLocaleString() }}
              </template>
            </span>
            upfront before work begins.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>