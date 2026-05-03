import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Currency, ExchangeRate, NbuRate, RateHistory, Statistic, RatesFilter, HistoryFilter, PaginationMeta } from '@/types'
import { fetchCurrencies } from '@/api/currencies'
import { fetchRates, fetchNbuRates } from '@/api/rates'
import { fetchHistory } from '@/api/history'
import { fetchStatistics } from '@/api/statistics'

export const useCurrencyStore = defineStore('currency', () => {
  const currencies = ref<Currency[]>([])
  const rates = ref<ExchangeRate[]>([])
  const nbuRates = ref<NbuRate[]>([])
  const history = ref<RateHistory[]>([])
  const historyMeta = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const statistics = ref<Statistic[]>([])

  const loadingCurrencies = ref(false)
  const loadingRates = ref(false)
  const loadingNbu = ref(false)
  const loadingHistory = ref(false)
  const loadingStatistics = ref(false)

  const error = ref<string | null>(null)

  async function loadCurrencies() {
    loadingCurrencies.value = true
    try {
      currencies.value = await fetchCurrencies()
    } catch {
      error.value = 'Не удалось загрузить валюты'
    } finally {
      loadingCurrencies.value = false
    }
  }

  async function loadRates(filter: RatesFilter = {}) {
    loadingRates.value = true
    error.value = null
    try {
      rates.value = await fetchRates(filter)
    } catch {
      error.value = 'Не удалось загрузить курсы'
    } finally {
      loadingRates.value = false
    }
  }

  async function loadNbuRates() {
    loadingNbu.value = true
    try {
      nbuRates.value = await fetchNbuRates()
    } catch {
      error.value = 'Не удалось загрузить курсы НБУ'
    } finally {
      loadingNbu.value = false
    }
  }

  async function loadHistory(filter: HistoryFilter = {}) {
    loadingHistory.value = true
    error.value = null
    try {
      const response = await fetchHistory(filter)
      history.value = response.data
      historyMeta.value = response.meta
    } catch {
      error.value = 'Не удалось загрузить историю'
    } finally {
      loadingHistory.value = false
    }
  }

  async function loadStatistics(filter: HistoryFilter = {}) {
    loadingStatistics.value = true
    error.value = null
    try {
      statistics.value = await fetchStatistics(filter)
    } catch {
      error.value = 'Не удалось загрузить статистику'
    } finally {
      loadingStatistics.value = false
    }
  }

  return {
    currencies,
    rates,
    nbuRates,
    history,
    historyMeta,
    statistics,
    loadingCurrencies,
    loadingRates,
    loadingNbu,
    loadingHistory,
    loadingStatistics,
    error,
    loadCurrencies,
    loadRates,
    loadNbuRates,
    loadHistory,
    loadStatistics,
  }
})
