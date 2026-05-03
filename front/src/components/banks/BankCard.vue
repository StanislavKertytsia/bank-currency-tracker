<script setup lang="ts">
import { RouterLink } from 'vue-router'
import type { Bank } from '@/types'

interface Props {
  bank: Bank
}

const props = defineProps<Props>()

function renderStars(rating: number | null): number[] {
  const r = Math.round(rating ?? 0)
  return Array.from({ length: 5 }, (_, i) => i + 1).map((i) => (i <= r ? 1 : 0))
}
</script>

<template>
  <RouterLink
    :to="`/banks/${props.bank.id}`"
    class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-primary-200 transition-all"
  >
    <div class="flex items-start gap-4">
      <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-slate-100 overflow-hidden">
        <img
          v-if="props.bank.logo_url"
          :src="props.bank.logo_url"
          :alt="props.bank.name"
          class="h-full w-full object-contain p-1"
        />
        <svg v-else class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
          />
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-slate-800 truncate">{{ props.bank.name }}</h3>

        <!-- Rating stars -->
        <div v-if="props.bank.rating" class="mt-1 flex items-center gap-0.5">
          <svg
            v-for="(filled, i) in renderStars(props.bank.rating)"
            :key="i"
            :class="filled ? 'text-amber-400' : 'text-slate-200'"
            class="h-3.5 w-3.5"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          <span class="ml-1 text-xs text-slate-500">{{ props.bank.rating?.toFixed(1) }}</span>
        </div>

        <p v-if="props.bank.phone" class="mt-2 text-xs text-slate-500 truncate">
          {{ props.bank.phone }}
        </p>
        <p v-if="props.bank.address" class="text-xs text-slate-400 truncate">
          {{ props.bank.address }}
        </p>
      </div>
    </div>
    <div class="mt-4 flex items-center justify-end">
      <span class="text-xs font-medium text-primary-600">Подробнее →</span>
    </div>
  </RouterLink>
</template>
