<script setup lang="ts">
interface Props {
  variant?: 'default' | 'success' | 'danger' | 'warning' | 'info'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
})

const variants: Record<string, string> = {
  default: 'bg-slate-100 text-slate-700',
  success: 'bg-green-100 text-green-700',
  danger: 'bg-red-100 text-red-700',
  warning: 'bg-amber-100 text-amber-700',
  info: 'bg-blue-100 text-blue-700',
}
</script>

<template>
  <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', variants[props.variant]]">
    <slot />
  </span>
</template>
