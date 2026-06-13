import { defineStore } from 'pinia';
import { ref } from 'vue';
import {
    products as productRoute,
    units as unitsRoute,
    templates as templatesRoute,
    accounts as accountsRoute,
    users as usersRoute,
    accountContacts as accountContactsRoute,
} from '@/routes/builder-data';

export const useBuilderDataStore = defineStore('builderData', () => {
    const products = ref<any[]>([]);
    const units = ref<any[]>([]);
    const templates = ref<any[]>([]);
    const accounts = ref<any[]>([]);
    const users = ref<any[]>([]);
    const accountContacts = ref<any[]>([]);
    const projects = ref<any[]>([]);

    const productsLoaded = ref(false);
    const unitsLoaded = ref(false);
    const templatesLoaded = ref(false);
    const accountsLoaded = ref(false);
    const usersLoaded = ref(false);
    const accountContactsLoaded = ref(false);
    const projectsLoaded = ref(false);

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

    const fetchUsers = async () => {
        if (usersLoaded.value) {
            return users.value;
        }

        const response = await fetch(usersRoute.get().url);
        users.value = await response.json();
        usersLoaded.value = true;

        return users.value;
    };

    const fetchAccountContacts = async (filters?: { account_id?: string }) => {
        if (accountContactsLoaded.value && !filters?.account_id) {
            return accountContacts.value;
        }

        const params = new URLSearchParams();
        if (filters?.account_id) params.append('account_id', filters.account_id);
        const url =
            accountContactsRoute.get().url +
            (params.toString() ? '?' + params.toString() : '');
        const response = await fetch(url);
        accountContacts.value = await response.json();
        accountContactsLoaded.value = true;

        return accountContacts.value;
    };

    const fetchProjects = async (filters?: { account_id?: string }) => {
        const params = new URLSearchParams();
        if (filters?.account_id) params.append('account_id', filters.account_id);

        const url = `/builder-data/projects${params.toString() ? '?' + params.toString() : ''}`;
        const response = await fetch(url);
        projects.value = await response.json();
        projectsLoaded.value = true;

        return projects.value;
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
        users.value = [];
        accountContacts.value = [];
        projects.value = [];
        productsLoaded.value = false;
        unitsLoaded.value = false;
        templatesLoaded.value = false;
        accountsLoaded.value = false;
        usersLoaded.value = false;
        accountContactsLoaded.value = false;
        projectsLoaded.value = false;
    };

    return {
        products,
        units,
        templates,
        accounts,
        users,
        accountContacts,
        projects,
        productsLoaded,
        unitsLoaded,
        templatesLoaded,
        accountsLoaded,
        usersLoaded,
        accountContactsLoaded,
        projectsLoaded,
        fetchProducts,
        fetchUnits,
        fetchTemplates,
        fetchAccounts,
        fetchUsers,
        fetchAccountContacts,
        fetchProjects,
        fetchTemplate,
        reset,
    };
});
