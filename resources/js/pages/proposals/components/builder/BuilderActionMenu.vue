<script setup lang="ts">
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <slot />
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-48">
      <DropdownMenuLabel>Proposal actions</DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuItem>Duplicate</DropdownMenuItem>
      <DropdownMenuItem>Save as template</DropdownMenuItem>
      <DropdownMenuItem>Download PDF</DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem class="text-destructive">Delete proposal</DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
