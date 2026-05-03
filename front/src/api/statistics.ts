import api from './index'
import type { Statistic, HistoryFilter } from '@/types'

export const fetchStatistics = async (filter: HistoryFilter = {}): Promise<Statistic[]> => {
  const params = Object.fromEntries(
    Object.entries(filter).filter(([, v]) => v != null && v !== ''),
  )
  const { data } = await api.get<{ data: Statistic[] }>('/statistics', { params })
  return data.data
}
