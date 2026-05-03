import api from './index'
import type { Currency } from '@/types'

export const fetchCurrencies = async (): Promise<Currency[]> => {
  const { data } = await api.get<{ data: Currency[] }>('/currencies')
  return data.data
}
