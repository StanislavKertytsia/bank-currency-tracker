import api from './index'
import type { Bank, BankDetail } from '@/types'

export const fetchBanks = async (): Promise<Bank[]> => {
  const { data } = await api.get<{ data: Bank[] }>('/banks')
  return data.data
}

export const fetchBank = async (id: number): Promise<BankDetail> => {
  const { data } = await api.get<{ data: BankDetail }>(`/banks/${id}`)
  return data.data
}
