<script setup lang="ts">
interface Option {
  value: string | number
  label: string
}

interface Props {
  modelValue?: string | number | null
  options: Option[]
  label?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  placeholder: 'Выберите...',
  disabled: false,
  required: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

function onChange(e: Event) {
  const val = (e.target as HTMLSelectElement).value
  emit('update:modelValue', val === '' ? null : isNaN(Number(val)) ? val : Number(val))
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="props.label" class="text-sm font-medium text-slate-700">
      {{ props.label }}
    </label>
    <select
      :value="props.modelValue ?? ''"
      :disabled="props.disabled"
      :class="[
        'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition',
        'focus:border-primary-500 focus:ring-2 focus:ring-primary-100',
        props.disabled ? 'cursor-not-allowed opacity-50' : '',
      ]"
      @change="onChange"
    >
      <option v-if="!props.required" value="">{{ props.placeholder }}</option>
      <option v-for="opt in props.options" :key="opt.value" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>
  </div>
</template>
