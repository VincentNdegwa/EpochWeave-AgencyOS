<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md"
    >
        <AppLogoIcon class="size-8 fill-current text-white dark:text-black" />
    </div>
    <div
        class="flex flex-col leading-none group-data-[collapsible=icon]:hidden"
    >
        <span class="text-md font-bold tracking-tight text-foreground">
            EpochWeave
        </span>
        <span
            class="text-[10px] font-medium tracking-wide text-muted-foreground uppercase"
        >
            Agency OS
        </span>
    </div>
</template>
