<script setup lang="ts">
import BaseSkeleton from '@/components/base/BaseSkeleton.vue'
import BaseBadge from '@/components/base/BaseBadge.vue'
import type { ExchangeRate, NbuRate } from '@/types'

interface Props {
  rates: ExchangeRate[]
  nbuRates?: NbuRate[]
  loading?: boolean
  showBank?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  nbuRates: () => [],
  loading: false,
  showBank: true,
})

function formatRate(rate: number | null | undefined): string {
  if (rate == null) return '—'
  return rate.toFixed(4)
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleString('uk-UA', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="relative overflow-x-auto">
    <!-- Refresh overlay: shown when reloading already-populated data -->
    <div
      v-if="loading && rates.length > 0"
      class="absolute inset-0 z-10 bg-white/60"
    />

    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50">
          <th v-if="showBank" class="px-4 py-3 text-left font-semibold text-slate-600">Банк</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600">Валюта</th>
          <th class="px-4 py-3 text-right font-semibold text-slate-600">Покупка</th>
          <th class="px-4 py-3 text-right font-semibold text-slate-600">Продажа</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600">Источник</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600">Обновлено</th>
        </tr>
      </thead>
      <tbody>
        <!-- First-load skeleton: only when no data yet -->
        <template v-if="loading && rates.length === 0">
          <tr v-for="n in 8" :key="n" class="border-b border-slate-100">
            <td v-if="showBank" class="px-4 py-3"><BaseSkeleton height="h-4" width="w-32" /></td>
            <td class="px-4 py-3"><BaseSkeleton height="h-4" width="w-16" /></td>
            <td class="px-4 py-3"><BaseSkeleton height="h-4" width="w-20" /></td>
            <td class="px-4 py-3"><BaseSkeleton height="h-4" width="w-20" /></td>
            <td class="px-4 py-3"><BaseSkeleton height="h-4" width="w-16" /></td>
            <td class="px-4 py-3"><BaseSkeleton height="h-4" width="w-24" /></td>
          </tr>
        </template>

        <!-- Data rows: shown when data exists (even during refresh) -->
        <template v-else>
          <tr
            v-for="rate in rates"
            :key="rate.id"
            class="border-b border-slate-100 hover:bg-slate-50 transition-colors"
          >
            <td v-if="showBank" class="px-4 py-3">
              <div class="flex items-center gap-2">
                <img
                  v-if="rate.bank?.logo_url"
                  :src="rate.bank.logo_url"
                  :alt="rate.bank.name"
                  class="h-6 w-6 rounded object-contain"
                />
                <span class="font-medium text-slate-800">{{ rate.bank?.name ?? '—' }}</span>
              </div>
            </td>
            <td class="px-4 py-3">
              <span class="font-semibold text-slate-700">{{ rate.currency?.code ?? '—' }}</span>
            </td>
            <td class="px-4 py-3 text-right font-mono text-green-700 font-semibold">
              {{ formatRate(rate.buy_rate) }}
            </td>
            <td class="px-4 py-3 text-right font-mono text-red-600 font-semibold">
              {{ formatRate(rate.sell_rate) }}
            </td>
            <td class="px-4 py-3">
              <BaseBadge :variant="rate.source === 'nbu' ? 'info' : 'default'">
                {{ rate.source }}
              </BaseBadge>
            </td>
            <td class="px-4 py-3 text-slate-500 text-xs">
              {{ formatDate(rate.updated_at) }}
            </td>
          </tr>

          <!-- NBU rates section -->
          <template v-if="nbuRates.length > 0">
            <tr class="border-t-2 border-blue-200 bg-blue-50">
              <td :colspan="showBank ? 6 : 5" class="px-4 py-2">
                <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">
                  Официальный курс НБУ
                </span>
              </td>
            </tr>
            <tr
              v-for="nbu in nbuRates"
              :key="nbu.currency_code"
              class="border-b border-blue-100 bg-blue-50/50"
            >
              <td v-if="showBank" class="px-4 py-3 text-slate-500 text-xs">НБУ</td>
              <td class="px-4 py-3">
                <span class="font-semibold text-slate-700">{{ nbu.currency_code }}</span>
              </td>
              <td class="px-4 py-3 text-right font-mono text-blue-700 font-semibold">
                {{ formatRate(nbu.rate) }}
              </td>
              <td class="px-4 py-3 text-right font-mono text-blue-700 font-semibold">
                {{ formatRate(nbu.rate) }}
              </td>
              <td class="px-4 py-3">
                <BaseBadge variant="info">НБУ</BaseBadge>
              </td>
              <td class="px-4 py-3 text-slate-500 text-xs">официальный</td>
            </tr>
            <!-- Average row -->
            <tr
              v-for="nbu in nbuRates.filter(n => n.avg_buy_rate != null || n.avg_sell_rate != null)"
              :key="`avg-${nbu.currency_code}`"
              class="border-b border-slate-100 bg-amber-50/50"
            >
              <td v-if="showBank" class="px-4 py-3 text-slate-500 text-xs">Среднее</td>
              <td class="px-4 py-3">
                <span class="font-semibold text-slate-700">{{ nbu.currency_code }}</span>
              </td>
              <td class="px-4 py-3 text-right font-mono text-amber-700 font-semibold">
                {{ formatRate(nbu.avg_buy_rate) }}
              </td>
              <td class="px-4 py-3 text-right font-mono text-amber-700 font-semibold">
                {{ formatRate(nbu.avg_sell_rate) }}
              </td>
              <td class="px-4 py-3">
                <BaseBadge variant="warning">Среднее</BaseBadge>
              </td>
              <td class="px-4 py-3 text-slate-500 text-xs">по банкам</td>
            </tr>
          </template>

          <tr v-if="rates.length === 0 && !loading">
            <td :colspan="showBank ? 6 : 5" class="px-4 py-12 text-center text-slate-500">
              Данные не найдены
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>
