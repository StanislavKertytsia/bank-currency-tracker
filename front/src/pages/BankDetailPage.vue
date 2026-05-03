<script setup lang="ts">
import { onMounted, onUnmounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import BaseCard from '@/components/base/BaseCard.vue'
import BaseSkeleton from '@/components/base/BaseSkeleton.vue'
import BaseBadge from '@/components/base/BaseBadge.vue'
import RatesTable from '@/components/rates/RatesTable.vue'
import BranchMap from '@/components/map/BranchMap.vue'
import { useBankStore } from '@/stores/bank'

const route = useRoute()
const bankStore = useBankStore()

const bankId = computed(() => {
  const raw = route.params['id']
  const str = Array.isArray(raw) ? raw[0] : raw
  return str ? Number(str) : NaN
})
const bank = computed(() => bankStore.selectedBank)

function renderStars(rating: number | null): number[] {
  const r = Math.round(rating ?? 0)
  return Array.from({ length: 5 }, (_, i) => i + 1).map((i) => (i <= r ? 1 : 0))
}

onMounted(() => {
  if (!isNaN(bankId.value)) bankStore.loadBank(bankId.value)
})

onUnmounted(() => {
  bankStore.clearSelectedBank()
})
</script>

<template>
  <div class="p-4 md:p-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
      <RouterLink to="/banks" class="hover:text-primary-600">Банки</RouterLink>
      <span>/</span>
      <span class="text-slate-800">{{ bank?.name ?? '...' }}</span>
    </nav>

    <!-- Skeleton -->
    <template v-if="bankStore.loading || (!bank && !bankStore.error)">
      <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex gap-6">
          <BaseSkeleton width="w-20" height="h-20" rounded="lg" />
          <div class="flex-1 space-y-3">
            <BaseSkeleton height="h-7" width="w-64" />
            <BaseSkeleton height="h-4" width="w-48" />
            <BaseSkeleton height="h-4" width="w-72" />
          </div>
        </div>
      </div>
    </template>

    <!-- Bank info -->
    <template v-else-if="bank">
      <BaseCard class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
          <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-slate-100 overflow-hidden">
            <img
              v-if="bank.logo_url"
              :src="bank.logo_url"
              :alt="bank.name"
              class="h-full w-full object-contain p-2"
            />
            <svg v-else class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <div class="flex flex-wrap items-start gap-3">
              <h1 class="text-2xl font-bold text-slate-800">{{ bank.name }}</h1>
              <div v-if="bank.rating" class="flex items-center gap-1 mt-1">
                <svg
                  v-for="(filled, i) in renderStars(bank.rating)"
                  :key="i"
                  :class="filled ? 'text-amber-400' : 'text-slate-200'"
                  class="h-4 w-4"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="text-sm text-slate-500">{{ bank.rating.toFixed(1) }}</span>
              </div>
            </div>

            <p v-if="bank.description" class="mt-2 text-sm text-slate-600">
              {{ bank.description }}
            </p>

            <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-500">
              <a
                v-if="bank.website"
                :href="bank.website"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-1 hover:text-primary-600"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                  />
                </svg>
                Сайт
              </a>
              <span v-if="bank.phone" class="flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                  />
                </svg>
                {{ bank.phone }}
              </span>
              <a
                v-if="bank.email"
                :href="`mailto:${bank.email}`"
                class="flex items-center gap-1 hover:text-primary-600"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
                {{ bank.email }}
              </a>
              <span v-if="bank.address" class="flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ bank.address }}
              </span>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Rates -->
      <BaseCard padding="none" class="mb-6">
        <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">Курсы обмена</h2>
          <BaseBadge>{{ bank.rates?.length ?? 0 }} записей</BaseBadge>
        </div>
        <RatesTable :rates="bank.rates ?? []" :show-bank="false" />
      </BaseCard>

      <!-- Map -->
      <BaseCard padding="none">
        <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">Отделения</h2>
          <BaseBadge>{{ bank.branches?.length ?? 0 }} отделений</BaseBadge>
        </div>
        <div class="p-4">
          <BranchMap :branches="bank.branches ?? []" height="450px" />
        </div>
      </BaseCard>
    </template>

    <!-- Error -->
    <div v-else-if="bankStore.error" class="py-16 text-center text-slate-500">
      {{ bankStore.error }}
    </div>
  </div>
</template>
