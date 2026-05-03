import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Bank, BankDetail } from '@/types'
import { fetchBanks, fetchBank } from '@/api/banks'

export const useBankStore = defineStore('bank', () => {
  const banks = ref<Bank[]>([])
  const selectedBank = ref<BankDetail | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function loadBanks() {
    loading.value = true
    error.value = null
    try {
      banks.value = await fetchBanks()
    } catch {
      error.value = 'Не удалось загрузить список банков'
    } finally {
      loading.value = false
    }
  }

  async function loadBank(id: number) {
    loading.value = true
    error.value = null
    selectedBank.value = null
    try {
      selectedBank.value = await fetchBank(id)
    } catch {
      error.value = 'Не удалось загрузить данные банка'
    } finally {
      loading.value = false
    }
  }

  function clearSelectedBank() {
    selectedBank.value = null
  }

  return { banks, selectedBank, loading, error, loadBanks, loadBank, clearSelectedBank }
})
