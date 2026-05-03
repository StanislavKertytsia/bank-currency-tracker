import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useBankStore } from '@/stores/bank'

vi.mock('@/api/banks', () => ({
  fetchBanks: vi.fn(),
  fetchBank: vi.fn(),
}))

import { fetchBanks, fetchBank } from '@/api/banks'

const mockBank = {
  id: 1,
  name: 'PrivatBank',
  logo_url: null,
  rating: 4.5,
  phone: '+380 800 300 500',
  email: 'info@privatbank.ua',
  description: 'Leading bank',
  website: 'https://privatbank.ua',
  address: 'Kyiv',
  slug: 'privatbank',
}

const mockBankDetail = {
  ...mockBank,
  rates: [],
  branches: [],
}

describe('useBankStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('loadBanks sets banks and loading becomes false after completion', async () => {
    vi.mocked(fetchBanks).mockResolvedValueOnce([mockBank])

    const store = useBankStore()
    await store.loadBanks()

    expect(store.banks).toEqual([mockBank])
    expect(store.loading).toBe(false)
  })

  it('loadBanks sets error on failure', async () => {
    vi.mocked(fetchBanks).mockRejectedValueOnce(new Error('Network error'))

    const store = useBankStore()
    await store.loadBanks()

    expect(store.error).toBeTruthy()
    expect(store.loading).toBe(false)
  })

  it('loadBank sets selectedBank', async () => {
    vi.mocked(fetchBank).mockResolvedValueOnce(mockBankDetail)

    const store = useBankStore()
    await store.loadBank(1)

    expect(store.selectedBank).toEqual(mockBankDetail)
    expect(store.loading).toBe(false)
  })

  it('loadBank sets error and selectedBank=null on failure', async () => {
    vi.mocked(fetchBank).mockRejectedValueOnce(new Error('Not found'))

    const store = useBankStore()
    await store.loadBank(999)

    expect(store.error).toBeTruthy()
    expect(store.selectedBank).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('clearSelectedBank resets selectedBank to null', async () => {
    vi.mocked(fetchBank).mockResolvedValueOnce(mockBankDetail)

    const store = useBankStore()
    await store.loadBank(1)
    expect(store.selectedBank).not.toBeNull()

    store.clearSelectedBank()
    expect(store.selectedBank).toBeNull()
  })
})
