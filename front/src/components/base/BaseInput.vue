<script setup lang="ts">
interface Props {
  modelValue?: string | number
  label?: string
  placeholder?: string
  type?: string
  error?: string | null
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="props.label" class="text-sm font-medium text-slate-700">
      {{ props.label }}
    </label>
    <input
      :value="props.modelValue"
      :type="props.type"
      :placeholder="props.placeholder"
      :disabled="props.disabled"
      :class="[
        'w-full rounded-lg border px-3 py-2 text-sm text-slate-800 outline-none transition placeholder:text-slate-400',
        'focus:border-primary-500 focus:ring-2 focus:ring-primary-100',
        props.error ? 'border-red-400' : 'border-slate-300',
        props.disabled ? 'bg-slate-50 cursor-not-allowed' : 'bg-white',
      ]"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <span v-if="props.error" class="text-xs text-red-500">{{ props.error }}</span>
  </div>
</template>
