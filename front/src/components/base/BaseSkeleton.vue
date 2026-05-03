<script setup lang="ts">
interface Props {
  width?: string
  height?: string
  rounded?: 'sm' | 'md' | 'lg' | 'full'
}

const props = withDefaults(defineProps<Props>(), {
  width: 'w-full',
  height: 'h-4',
  rounded: 'md',
})

const roundedMap: Record<string, string> = {
  sm: 'rounded',
  md: 'rounded-md',
  lg: 'rounded-lg',
  full: 'rounded-full',
}
</script>

<template>
  <div
    :class="[
      'animate-pulse bg-slate-200',
      props.width,
      props.height,
      roundedMap[props.rounded],
    ]"
  />
</template>
