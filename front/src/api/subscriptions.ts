import api from './index'
import type { Subscription } from '@/types'

interface SubscriptionPayload {
  bank_id?: number | null
  currency_id?: number | null
}

export const getSubscriptions = async (): Promise<Subscription[]> => {
  const { data } = await api.get<{ data: Subscription[] }>('/subscriptions')
  return data.data
}

export const addSubscription = async (payload: SubscriptionPayload): Promise<Subscription> => {
  const { data } = await api.post<{ data: Subscription }>('/subscriptions', payload)
  return data.data
}

export const removeSubscription = async (id: number): Promise<void> => {
  await api.delete(`/subscriptions/${id}`)
}
