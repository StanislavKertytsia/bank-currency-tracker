import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Subscription } from '@/types'
import { getSubscriptions, addSubscription, removeSubscription } from '@/api/subscriptions'

export const useNotificationStore = defineStore('notification', () => {
  const subscriptions = ref<Subscription[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function loadSubscriptions() {
    loading.value = true
    error.value = null
    try {
      subscriptions.value = await getSubscriptions()
    } catch {
      error.value = 'Не удалось загрузить подписки'
    } finally {
      loading.value = false
    }
  }

  async function subscribe(bankId: number | null, currencyId: number | null) {
    loading.value = true
    error.value = null
    try {
      const sub = await addSubscription({ bank_id: bankId, currency_id: currencyId })
      subscriptions.value.push(sub)
    } catch {
      error.value = 'Не удалось добавить подписку'
    } finally {
      loading.value = false
    }
  }

  async function unsubscribe(id: number) {
    loading.value = true
    error.value = null
    try {
      await removeSubscription(id)
      subscriptions.value = subscriptions.value.filter((s) => s.id !== id)
    } catch {
      error.value = 'Не удалось удалить подписку'
    } finally {
      loading.value = false
    }
  }

  return { subscriptions, loading, error, loadSubscriptions, subscribe, unsubscribe }
})
