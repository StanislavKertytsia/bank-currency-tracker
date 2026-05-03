<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSkeleton from '@/components/base/BaseSkeleton.vue'
import BankCard from '@/components/banks/BankCard.vue'
import { useBankStore } from '@/stores/bank'

const bankStore = useBankStore()
const search = ref('')
const pending = ref(true)

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  if (!q) return bankStore.banks
  return bankStore.banks.filter(
    (b) =>
      b.name.toLowerCase().includes(q) ||
      b.address?.toLowerCase().includes(q),
  )
})

onMounted(async () => {
  if (bankStore.banks.length === 0) await bankStore.loadBanks()
  pending.value = false
})
</script>

<template>
  <div class="p-4 md:p-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Банки</h1>
        <p class="mt-1 text-sm text-slate-500">{{ bankStore.banks.length }} банков в системе</p>
      </div>
      <div class="w-full sm:w-72">
        <BaseInput
          v-model="search"
          placeholder="Поиск банка..."
          type="search"
        />
      </div>
    </div>

    <!-- Skeleton -->
    <div v-if="pending || bankStore.loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="n in 6" :key="n" class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex gap-4">
          <BaseSkeleton width="w-14" height="h-14" rounded="lg" />
          <div class="flex-1 space-y-2">
            <BaseSkeleton height="h-5" width="w-3/4" />
            <BaseSkeleton height="h-3" width="w-1/2" />
            <BaseSkeleton height="h-3" width="w-2/3" />
          </div>
        </div>
      </div>
    </div>

    <!-- Banks grid -->
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <BankCard v-for="bank in filtered" :key="bank.id" :bank="bank" />
      <div
        v-if="filtered.length === 0"
        class="col-span-full py-16 text-center text-slate-500"
      >
        Банки не найдены
      </div>
    </div>
  </div>
</template>
