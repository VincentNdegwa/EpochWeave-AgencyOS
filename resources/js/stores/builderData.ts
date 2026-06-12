import { defineStore } from 'pinia';
import { ref } from 'vue';
import { products as productRoute, units as unitsRoute, templates as templatesRoute, accounts as accountsRoute} from '@/routes/builder-data';

export const useBuilderDataStore = defineStore('builderData', () => {
  const products = ref<any[]>([]);
  const units = ref<any[]>([]);
  const templates = ref<any[]>([]);
  const accounts = ref<any[]>([]);

  const productsLoaded = ref(false);
  const unitsLoaded = ref(false);
  const templatesLoaded = ref(false);
  const accountsLoaded = ref(false);

  const fetchProducts = async () => {
    if (productsLoaded.value) {
return products.value;
}

    const response = await fetch(productRoute.get().url);
    products.value = await response.json();
    productsLoaded.value = true;

    return products.value;
  };

  const fetchUnits = async () => {
    if (unitsLoaded.value) {
return units.value;
}

    const response = await fetch(unitsRoute.get().url);
    units.value = await response.json();
    unitsLoaded.value = true;

    return units.value;
  };

  const fetchTemplates = async () => {
    if (templatesLoaded.value) {
return templates.value;
}

    const response = await fetch(templatesRoute.get().url);
    templates.value = await response.json();
    templatesLoaded.value = true;

    return templates.value;
  };

  const fetchAccounts = async () => {
    if (accountsLoaded.value) {
return accounts.value;
}

    const response = await fetch(accountsRoute.get().url);
    accounts.value = await response.json();
    accountsLoaded.value = true;

    return accounts.value;
  };

  const fetchTemplate = async (templateId: number) => {
    const response = await fetch(`/builder-data/templates/${templateId}`);

    if (!response.ok) {
      throw new Error('Failed to fetch template');
    }

    return await response.json();
  };

  const reset = () => {
    products.value = [];
    units.value = [];
    templates.value = [];
    accounts.value = [];
    productsLoaded.value = false;
    unitsLoaded.value = false;
    templatesLoaded.value = false;
    accountsLoaded.value = false;
  };

  return {
    products,
    units,
    templates,
    accounts,
    productsLoaded,
    unitsLoaded,
    templatesLoaded,
    accountsLoaded,
    fetchProducts,
    fetchUnits,
    fetchTemplates,
    fetchAccounts,
    fetchTemplate,
    reset,
  };
});
