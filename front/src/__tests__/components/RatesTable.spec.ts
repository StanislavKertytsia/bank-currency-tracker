import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import RatesTable from '@/components/rates/RatesTable.vue'
import type { ExchangeRate, NbuRate } from '@/types'

// BaseSkeleton uses animated placeholder — no external deps needed
vi.mock('leaflet', () => ({}))

const makeRate = (id: number, bankName = 'PrivatBank', currencyCode = 'USD'): ExchangeRate => ({
  id,
  bank_id: 1,
  currency_id: 1,
  buy_rate: 39.5,
  sell_rate: 40.1,
  source: 'minfin',
  updated_at: '2026-05-01T12:00:00Z',
  bank: { id: 1, name: bankName, logo_url: null },
  currency: { id: 1, code: currencyCode, name: 'US Dollar' },
})

const makeNbuRate = (code = 'USD'): NbuRate => ({
  currency_code: code,
  currency_name: 'US Dollar',
  rate: 41.2,
  avg_buy_rate: 39.8,
  avg_sell_rate: 40.3,
})

describe('RatesTable', () => {
  it('renders skeleton rows when loading=true and rates=[]', () => {
    const wrapper = mount(RatesTable, {
      props: { rates: [], loading: true },
    })
    // Skeleton rows are rendered inside v-for n in 8
    const skeletonRows = wrapper.findAll('tbody tr')
    expect(skeletonRows.length).toBe(8)
  })

  it('does not show skeleton when loading=false', () => {
    const wrapper = mount(RatesTable, {
      props: { rates: [], loading: false },
    })
    // No skeleton component present
    const skeletons = wrapper.findAllComponents({ name: 'BaseSkeleton' })
    expect(skeletons.length).toBe(0)
  })

  it('renders data rows for each rate', () => {
    const rates = [makeRate(1), makeRate(2)]
    const wrapper = mount(RatesTable, {
      props: { rates, loading: false },
    })
    const dataRows = wrapper.findAll('tbody tr')
    // At least 2 data rows (might have "no data" row removed)
    expect(dataRows.length).toBeGreaterThanOrEqual(2)
  })

  it('shows "Данные не найдены" when rates=[] and loading=false', () => {
    const wrapper = mount(RatesTable, {
      props: { rates: [], loading: false },
    })
    expect(wrapper.text()).toContain('Данные не найдены')
  })

  it('shows Банк column when showBank=true (default)', () => {
    const wrapper = mount(RatesTable, {
      props: { rates: [], loading: false },
    })
    expect(wrapper.text()).toContain('Банк')
  })

  it('hides Банк column when showBank=false', () => {
    const wrapper = mount(RatesTable, {
      props: { rates: [], loading: false, showBank: false },
    })
    expect(wrapper.text()).not.toContain('Банк')
  })

  it('shows NBU section when nbuRates is not empty', () => {
    const rates = [makeRate(1)]
    const nbuRates = [makeNbuRate('USD')]
    const wrapper = mount(RatesTable, {
      props: { rates, loading: false, nbuRates },
    })
    expect(wrapper.text()).toContain('Официальный курс НБУ')
  })

  it('does not show NBU section when nbuRates is empty', () => {
    const rates = [makeRate(1)]
    const wrapper = mount(RatesTable, {
      props: { rates, loading: false, nbuRates: [] },
    })
    expect(wrapper.text()).not.toContain('Официальный курс НБУ')
  })

  it('formats buy_rate with 4 decimal places', () => {
    const rate = makeRate(1)
    rate.buy_rate = 39.5
    const wrapper = mount(RatesTable, {
      props: { rates: [rate], loading: false },
    })
    expect(wrapper.text()).toContain('39.5000')
  })

  it('shows overlay when loading=true and rates.length > 0', () => {
    const rates = [makeRate(1)]
    const wrapper = mount(RatesTable, {
      props: { rates, loading: true },
    })
    // The overlay div has class bg-white/60 and z-10
    const overlay = wrapper.find('.bg-white\\/60')
    expect(overlay.exists()).toBe(true)
  })

  it('does not show overlay when loading=false', () => {
    const rates = [makeRate(1)]
    const wrapper = mount(RatesTable, {
      props: { rates, loading: false },
    })
    const overlay = wrapper.find('.bg-white\\/60')
    expect(overlay.exists()).toBe(false)
  })
})
