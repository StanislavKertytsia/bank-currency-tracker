<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseCard from '@/components/base/BaseCard.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseSkeleton from '@/components/base/BaseSkeleton.vue'
import RatesTable from '@/components/rates/RatesTable.vue'
import { useCurrencyStore } from '@/stores/currency'
import { useBankStore } from '@/stores/bank'

const route = useRoute()
const router = useRouter()
const currencyStore = useCurrencyStore()
const bankStore = useBankStore()

function parseId(value: unknown): number | null {
  const n = Number(value)
  return Number.isInteger(n) && n > 0 ? n : null
}

const selectedBankId = ref<number | null>(parseId(route.query.bank_id))
const selectedCurrencyId = ref<number | null>(parseId(route.query.currency_id))
const pending = ref(true)

const bankOptions = computed(() =>
  bankStore.banks.map((b) => ({ value: b.id, label: b.name })),
)

const currencyOptions = computed(() =>
  currencyStore.currencies.map((c) => ({ value: c.id, label: `${c.code} — ${c.name}` })),
)

async function loadData() {
  await currencyStore.loadRates({
    bank_id: selectedBankId.value,
    currency_id: selectedCurrencyId.value,
  })
}

function syncQuery() {
  const query: Record<string, string> = {}
  if (selectedBankId.value) query.bank_id = String(selectedBankId.value)
  if (selectedCurrencyId.value) query.currency_id = String(selectedCurrencyId.value)
  router.replace({ query })
}

function resetFilters() {
  selectedBankId.value = null
  selectedCurrencyId.value = null
}

watch([selectedBankId, selectedCurrencyId], () => {
  syncQuery()
  loadData()
})

onMounted(async () => {
  await Promise.all([
    bankStore.loadBanks(),
    currencyStore.loadCurrencies(),
    currencyStore.loadNbuRates(),
    loadData(),
  ])
  pending.value = false
})

const lastUpdated = computed(() => {
  const rate = currencyStore.rates[0]
  if (!rate) return null
  return new Date(rate.updated_at).toLocaleString('uk-UA')
})

const filteredNbuRates = computed(() => {
  if (!selectedCurrencyId.value) return currencyStore.nbuRates
  const currency = currencyStore.currencies.find((c) => c.id === selectedCurrencyId.value)
  if (!currency) return currencyStore.nbuRates
  return currencyStore.nbuRates.filter((n) => n.currency_code === currency.code)
})
</script>

<template>
  <div class="p-4 md:p-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">Курсы валют</h1>
      <p class="mt-1 text-sm text-slate-500">
        {{ lastUpdated ? `Обновлено: ${lastUpdated}` : ' ' }}
      </p>
    </div>

    <!-- Filters -->
    <BaseCard padding="sm" class="mb-6">
      <div class="flex flex-wrap items-end gap-4">
        <template v-if="pending">
          <div class="flex w-full flex-col gap-1 sm:w-56">
            <BaseSkeleton height="h-4" width="w-10" />
            <BaseSkeleton height="h-9" />
          </div>
          <div class="flex w-full flex-col gap-1 sm:w-56">
            <BaseSkeleton height="h-4" width="w-14" />
            <BaseSkeleton height="h-9" />
          </div>
          <BaseSkeleton height="h-9" width="w-24" />
          <BaseSkeleton height="h-9" width="w-24" />
        </template>
        <template v-else>
          <div class="w-full sm:w-56">
            <BaseSelect
              v-model="selectedBankId"
              :options="bankOptions"
              label="Банк"
              placeholder="Все банки"
            />
          </div>
          <div class="w-full sm:w-56">
            <BaseSelect
              v-model="selectedCurrencyId"
              :options="currencyOptions"
              label="Валюта"
              placeholder="Все валюты"
            />
          </div>
          <BaseButton variant="secondary" size="md" @click="resetFilters">
            Сбросить
          </BaseButton>
          <BaseButton size="md" :loading="currencyStore.loadingRates" @click="loadData">
            Обновить
          </BaseButton>
        </template>
      </div>
    </BaseCard>

    <!-- Rates table -->
    <BaseCard padding="none">
      <div class="border-b border-slate-200 px-6 py-4">
        <h2 class="font-semibold text-slate-800">Актуальные курсы</h2>
      </div>
      <RatesTable
        :rates="currencyStore.rates"
        :nbu-rates="filteredNbuRates"
        :loading="pending || currencyStore.loadingRates"
        :show-bank="!selectedBankId"
      />
    </BaseCard>
  </div>
</template>
