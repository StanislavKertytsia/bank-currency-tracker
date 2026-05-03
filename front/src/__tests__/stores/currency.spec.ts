import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCurrencyStore } from '@/stores/currency'

vi.mock('@/api/currencies', () => ({
  fetchCurrencies: vi.fn(),
}))

vi.mock('@/api/rates', () => ({
  fetchRates: vi.fn(),
  fetchNbuRates: vi.fn(),
}))

vi.mock('@/api/history', () => ({
  fetchHistory: vi.fn(),
}))

vi.mock('@/api/statistics', () => ({
  fetchStatistics: vi.fn(),
}))

import { fetchCurrencies } from '@/api/currencies'
import { fetchRates, fetchNbuRates } from '@/api/rates'
import { fetchHistory } from '@/api/history'
import { fetchStatistics } from '@/api/statistics'

const mockCurrencies = [
  { id: 1, code: 'USD', name: 'US Dollar' },
  { id: 2, code: 'EUR', name: 'Euro' },
]

const mockRates = [
  {
    id: 1,
    bank_id: 1,
    currency_id: 1,
    buy_rate: 39.5,
    sell_rate: 40.1,
    source: 'minfin',
    updated_at: '2026-05-01T00:00:00Z',
  },
]

const mockNbuRates = [
  {
    currency_code: 'USD',
    currency_name: 'US Dollar',
    rate: 41.2,
    avg_buy_rate: 39.8,
    avg_sell_rate: 40.3,
  },
]

const mockHistory = {
  data: [
    {
      id: 1,
      bank_id: 1,
      currency_id: 1,
      buy_rate: 43.0,
      sell_rate: 44.0,
      previous_buy_rate: 40.0,
      previous_sell_rate: 41.0,
      change_percent: 7.5,
      source: 'minfin',
      recorded_at: '2026-05-01T00:00:00Z',
    },
  ],
  meta: {
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 1,
  },
}

const mockStatistics = [
  {
    recorded_at: '2026-05-01T00:00:00Z',
    buy_rate: 39.5,
    sell_rate: 40.1,
    currency_id: 1,
    currency_code: 'USD',
    bank_id: 1,
  },
]

describe('useCurrencyStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('loadCurrencies sets currencies', async () => {
    vi.mocked(fetchCurrencies).mockResolvedValueOnce(mockCurrencies)

    const store = useCurrencyStore()
    await store.loadCurrencies()

    expect(store.currencies).toEqual(mockCurrencies)
    expect(store.loadingCurrencies).toBe(false)
  })

  it('loadCurrencies sets error on failure', async () => {
    vi.mocked(fetchCurrencies).mockRejectedValueOnce(new Error('API error'))

    const store = useCurrencyStore()
    await store.loadCurrencies()

    expect(store.error).toBeTruthy()
  })

  it('loadRates sets rates', async () => {
    vi.mocked(fetchRates).mockResolvedValueOnce(mockRates)

    const store = useCurrencyStore()
    await store.loadRates()

    expect(store.rates).toEqual(mockRates)
    expect(store.loadingRates).toBe(false)
  })

  it('loadRates sets error on failure', async () => {
    vi.mocked(fetchRates).mockRejectedValueOnce(new Error('API error'))

    const store = useCurrencyStore()
    await store.loadRates()

    expect(store.error).toBeTruthy()
  })

  it('loadNbuRates sets nbuRates', async () => {
    vi.mocked(fetchNbuRates).mockResolvedValueOnce(mockNbuRates)

    const store = useCurrencyStore()
    await store.loadNbuRates()

    expect(store.nbuRates).toEqual(mockNbuRates)
    expect(store.loadingNbu).toBe(false)
  })

  it('loadNbuRates sets error on failure', async () => {
    vi.mocked(fetchNbuRates).mockRejectedValueOnce(new Error('API error'))

    const store = useCurrencyStore()
    await store.loadNbuRates()

    expect(store.error).toBeTruthy()
  })

  it('loadHistory sets history and historyMeta', async () => {
    vi.mocked(fetchHistory).mockResolvedValueOnce(mockHistory)

    const store = useCurrencyStore()
    await store.loadHistory()

    expect(store.history).toEqual(mockHistory.data)
    expect(store.historyMeta).toEqual(mockHistory.meta)
    expect(store.loadingHistory).toBe(false)
  })

  it('loadHistory sets error on failure', async () => {
    vi.mocked(fetchHistory).mockRejectedValueOnce(new Error('API error'))

    const store = useCurrencyStore()
    await store.loadHistory()

    expect(store.error).toBeTruthy()
  })

  it('loadStatistics sets statistics', async () => {
    vi.mocked(fetchStatistics).mockResolvedValueOnce(mockStatistics)

    const store = useCurrencyStore()
    await store.loadStatistics()

    expect(store.statistics).toEqual(mockStatistics)
    expect(store.loadingStatistics).toBe(false)
  })

  it('loadStatistics sets error on failure', async () => {
    vi.mocked(fetchStatistics).mockRejectedValueOnce(new Error('API error'))

    const store = useCurrencyStore()
    await store.loadStatistics()

    expect(store.error).toBeTruthy()
  })
})
