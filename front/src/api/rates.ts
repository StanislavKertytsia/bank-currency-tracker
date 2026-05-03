import api from './index'
import type { ExchangeRate, NbuRate, RatesFilter } from '@/types'

export const fetchRates = async (filter: RatesFilter = {}): Promise<ExchangeRate[]> => {
  const params = Object.fromEntries(
    Object.entries(filter).filter(([, v]) => v != null),
  )
  const { data } = await api.get<{ data: ExchangeRate[] }>('/rates', { params })
  return data.data
}

export const fetchNbuRates = async (): Promise<NbuRate[]> => {
  const { data } = await api.get<{ data: { nbu: any[]; averages: any[] } }>('/rates/nbu')
  const { nbu, averages } = data.data

  const avgBuyMap = new Map<number, number>(
    averages.map((a: any) => [a.currency_id, a.avg_buy_rate]),
  )
  const avgSellMap = new Map<number, number>(
    averages.map((a: any) => [a.currency_id, a.avg_sell_rate]),
  )

  const seen = new Set<string>()
  return nbu
    .filter((r: any) => {
      if (seen.has(r.currency?.code)) return false
      seen.add(r.currency?.code)
      return true
    })
    .map((r: any) => ({
      currency_code: r.currency?.code ?? '',
      currency_name: r.currency?.name ?? '',
      rate: r.buy_rate,
      avg_buy_rate: avgBuyMap.get(r.currency?.id) ?? null,
      avg_sell_rate: avgSellMap.get(r.currency?.id) ?? null,
    }))
}
