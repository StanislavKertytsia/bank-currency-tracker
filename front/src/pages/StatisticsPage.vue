<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseCard from '@/components/base/BaseCard.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSkeleton from '@/components/base/BaseSkeleton.vue'
import RateChart from '@/components/charts/RateChart.vue'
import { useCurrencyStore } from '@/stores/currency'
import { useBankStore } from '@/stores/bank'

const currencyStore = useCurrencyStore()
const bankStore = useBankStore()
const route = useRoute()
const router = useRouter()
const pending = ref(true)

const today = new Date().toISOString().slice(0, 10)
const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10)

const from = ref<string>((route.query.from as string) || monthAgo)
const to = ref<string>((route.query.to as string) || today)
const selectedBankId = ref<number | null>(route.query.bank_id ? Number(route.query.bank_id) : null)
const selectedCurrencyId = ref<number | null>(
  route.query.currency_id ? Number(route.query.currency_id) : null,
)
const historyPage = ref(1)

const bankOptions = computed(() =>
  bankStore.banks.map((b) => ({ value: b.id, label: b.name })),
)
const currencyOptions = computed(() =>
  currencyStore.currencies.map((c) => ({ value: c.id, label: `${c.code} — ${c.name}` })),
)

const meta = computed(() => currencyStore.historyMeta)

function syncUrl() {
  router.replace({
    query: {
      from: from.value,
      to: to.value,
      ...(selectedBankId.value != null ? { bank_id: String(selectedBankId.value) } : {}),
      ...(selectedCurrencyId.value != null ? { currency_id: String(selectedCurrencyId.value) } : {}),
    },
  })
}

async function loadHistory() {
  await currencyStore.loadHistory({
    from: from.value,
    to: to.value,
    bank_id: selectedBankId.value,
    currency_id: selectedCurrencyId.value,
    page: historyPage.value,
    per_page: 15,
  })
}

async function loadStats() {
  const filter = {
    from: from.value,
    to: to.value,
    bank_id: selectedBankId.value,
    currency_id: selectedCurrencyId.value,
  }
  await Promise.all([
    currencyStore.loadStatistics(filter),
    loadHistory(),
  ])
}

function goToPage(page: number) {
  historyPage.value = page
  loadHistory()
}

// Prevents the watcher from triggering loadStats during initial setup
const initialized = ref(false)

watch([from, to, selectedBankId, selectedCurrencyId], () => {
  if (!initialized.value) return
  historyPage.value = 1
  syncUrl()
  loadStats()
})

onMounted(async () => {
  syncUrl()
  await Promise.all([
    bankStore.banks.length === 0 ? bankStore.loadBanks() : Promise.resolve(),
    currencyStore.currencies.length === 0 ? currencyStore.loadCurrencies() : Promise.resolve(),
  ])

  // Auto-select first bank if none came from URL
  if (selectedBankId.value === null && bankOptions.value.length > 0) {
    selectedBankId.value = bankOptions.value[0].value as number
  }

  initialized.value = true
  await loadStats()
  pending.value = false
})

const chartTitle = computed(() => {
  const bank = bankStore.banks.find((b) => b.id === selectedBankId.value)
  const currency = currencyStore.currencies.find((c) => c.id === selectedCurrencyId.value)
  const parts: string[] = []
  if (bank) parts.push(bank.name)
  if (currency) parts.push(currency.code)
  return parts.length > 0 ? parts.join(' — ') : 'Все банки / Все валюты'
})

const visiblePages = computed(() => {
  const total = meta.value.last_page
  const current = meta.value.current_page
  const pages: (number | '...')[] = []

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
    return pages
  }

  pages.push(1)
  if (current > 3) pages.push('...')
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
    pages.push(i)
  }
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})
</script>

<template>
  <div class="p-4 md:p-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Статистика курсов</h1>
      <p class="mt-1 text-sm text-slate-500">История изменений курсов валют</p>
    </div>

    <!-- Filters -->
    <BaseCard padding="sm" class="mb-6">
      <div class="flex flex-wrap items-end gap-4">
        <template v-if="pending">
          <div class="flex w-full flex-col gap-1 sm:w-40">
            <BaseSkeleton height="h-4" width="w-12" />
            <BaseSkeleton height="h-9" />
          </div>
          <div class="flex w-full flex-col gap-1 sm:w-40">
            <BaseSkeleton height="h-4" width="w-14" />
            <BaseSkeleton height="h-9" />
          </div>
          <div class="flex w-full flex-col gap-1 sm:w-52">
            <BaseSkeleton height="h-4" width="w-8" />
            <BaseSkeleton height="h-9" />
          </div>
          <div class="flex w-full flex-col gap-1 sm:w-52">
            <BaseSkeleton height="h-4" width="w-14" />
            <BaseSkeleton height="h-9" />
          </div>
          <BaseSkeleton height="h-9" width="w-24" />
        </template>
        <template v-else>
          <div class="w-full sm:w-40">
            <BaseInput
              v-model="from"
              label="С даты"
              type="date"
            />
          </div>
          <div class="w-full sm:w-40">
            <BaseInput
              v-model="to"
              label="По дату"
              type="date"
            />
          </div>
          <div class="w-full sm:w-52">
            <BaseSelect
              v-model="selectedBankId"
              :options="bankOptions"
              label="Банк"
              required
            />
          </div>
          <div class="w-full sm:w-52">
            <BaseSelect
              v-model="selectedCurrencyId"
              :options="currencyOptions"
              label="Валюта"
              placeholder="Все валюты"
            />
          </div>
          <BaseButton :loading="currencyStore.loadingStatistics" @click="loadStats">
            Применить
          </BaseButton>
        </template>
      </div>
    </BaseCard>

    <!-- Chart -->
    <BaseCard>
      <h2 class="mb-4 font-semibold text-slate-800">{{ chartTitle }}</h2>
      <RateChart
        :data="currencyStore.statistics"
        :loading="pending || currencyStore.loadingStatistics"
      />
    </BaseCard>

    <!-- History table -->
    <BaseCard padding="none" class="mt-6">
      <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
        <h2 class="font-semibold text-slate-800">Значительные изменения</h2>
        <span v-if="meta.total > 0" class="text-sm text-slate-500">
          Всего: {{ meta.total }}
        </span>
      </div>
      <div class="relative overflow-x-auto">
        <!-- Refresh overlay: shown when reloading already-populated data -->
        <div
          v-if="!pending && currencyStore.loadingHistory && currencyStore.history.length > 0"
          class="absolute inset-0 z-10 bg-white/60"
        />

        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50">
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Дата</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Банк</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Валюта</th>
              <th class="px-4 py-3 text-right font-semibold text-slate-600">Покупка</th>
              <th class="px-4 py-3 text-right font-semibold text-slate-600">Продажа</th>
              <th class="px-4 py-3 text-right font-semibold text-slate-600">Изменение</th>
            </tr>
          </thead>
          <tbody>
            <!-- First-load skeleton: only when no data yet -->
            <template v-if="(pending || currencyStore.loadingHistory) && currencyStore.history.length === 0">
              <tr v-for="n in 5" :key="n" class="border-b border-slate-100">
                <td v-for="m in 6" :key="m" class="px-4 py-3">
                  <div class="h-4 w-24 animate-pulse rounded bg-slate-200" />
                </td>
              </tr>
            </template>

            <!-- Data rows: shown when data exists (even during refresh) -->
            <template v-else>
              <tr
                v-for="item in currencyStore.history"
                :key="item.id"
                class="border-b border-slate-100 hover:bg-slate-50"
              >
                <td class="px-4 py-3 text-slate-500 text-xs">
                  {{ new Date(item.recorded_at).toLocaleString('uk-UA') }}
                </td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ item.bank?.name ?? '—' }}</td>
                <td class="px-4 py-3 font-semibold text-slate-700">{{ item.currency?.code ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-mono text-green-700">{{ item.buy_rate.toFixed(4) }}</td>
                <td class="px-4 py-3 text-right font-mono text-red-600">{{ item.sell_rate.toFixed(4) }}</td>
                <td class="px-4 py-3 text-right">
                  <span
                    :class="item.change_percent >= 0 ? 'text-red-600' : 'text-green-600'"
                    class="font-semibold"
                  >
                    {{ item.change_percent >= 0 ? '+' : '' }}{{ item.change_percent.toFixed(2) }}%
                  </span>
                </td>
              </tr>
              <tr v-if="currencyStore.history.length === 0 && !currencyStore.loadingHistory">
                <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                  Нет данных об изменениях курса
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="meta.last_page > 1"
        class="flex items-center justify-between border-t border-slate-200 px-6 py-4"
      >
        <p class="text-sm text-slate-500">
          Стр. {{ meta.current_page }} из {{ meta.last_page }}
        </p>
        <div class="flex items-center gap-1">
          <button
            :disabled="meta.current_page === 1 || currencyStore.loadingHistory"
            class="flex h-8 w-8 items-center justify-center rounded text-sm text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
            @click="goToPage(meta.current_page - 1)"
          >
            ‹
          </button>
          <template v-for="p in visiblePages" :key="p">
            <span v-if="p === '...'" class="flex h-8 w-8 items-center justify-center text-sm text-slate-400">…</span>
            <button
              v-else
              :disabled="currencyStore.loadingHistory"
              :class="[
                'flex h-8 w-8 items-center justify-center rounded text-sm',
                p === meta.current_page
                  ? 'bg-blue-600 font-semibold text-white'
                  : 'text-slate-600 hover:bg-slate-100',
              ]"
              @click="goToPage(p as number)"
            >
              {{ p }}
            </button>
          </template>
          <button
            :disabled="meta.current_page === meta.last_page || currencyStore.loadingHistory"
            class="flex h-8 w-8 items-center justify-center rounded text-sm text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
            @click="goToPage(meta.current_page + 1)"
          >
            ›
          </button>
        </div>
      </div>
    </BaseCard>
  </div>
</template>
