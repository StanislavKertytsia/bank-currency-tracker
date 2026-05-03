import api from './index'
import type { RateHistory, HistoryFilter, PaginationMeta } from '@/types'

export interface HistoryResponse {
  data: RateHistory[]
  meta: PaginationMeta
}

export const fetchHistory = async (filter: HistoryFilter = {}): Promise<HistoryResponse> => {
  const params = Object.fromEntries(
    Object.entries(filter).filter(([, v]) => v != null && v !== ''),
  )
  const { data } = await api.get<HistoryResponse>('/history', { params })
  return data
}
