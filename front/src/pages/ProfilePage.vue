<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import BaseCard from '@/components/base/BaseCard.vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import BaseBadge from '@/components/base/BaseBadge.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { useBankStore } from '@/stores/bank'
import { useCurrencyStore } from '@/stores/currency'

const router = useRouter()
const authStore = useAuthStore()
const notifStore = useNotificationStore()
const bankStore = useBankStore()
const currencyStore = useCurrencyStore()

const name = ref('')
const email = ref('')
const saveSuccess = ref(false)

const bankOptions = computed(() =>
  bankStore.banks.map((b) => ({ value: b.id, label: b.name })),
)
const currencyOptions = computed(() =>
  currencyStore.currencies.map((c) => ({ value: c.id, label: `${c.code} — ${c.name}` })),
)

const newSubBankId = ref<number | null>(null)
const newSubCurrencyId = ref<number | null>(null)

onMounted(async () => {
  await authStore.fetchProfile()
  name.value = authStore.user?.name ?? ''
  email.value = authStore.user?.email ?? ''
  await Promise.all([
    notifStore.loadSubscriptions(),
    bankStore.banks.length === 0 ? bankStore.loadBanks() : Promise.resolve(),
    currencyStore.currencies.length === 0 ? currencyStore.loadCurrencies() : Promise.resolve(),
  ])
})

async function saveProfile() {
  await authStore.updateProfile({ name: name.value })
  saveSuccess.value = true
  setTimeout(() => (saveSuccess.value = false), 3000)
}

async function toggleNotifications() {
  await authStore.toggleNotifications(!authStore.user?.notification_enabled)
}

async function addSubscription() {
  if (!newSubBankId.value && !newSubCurrencyId.value) return
  await notifStore.subscribe(newSubBankId.value, newSubCurrencyId.value)
  newSubBankId.value = null
  newSubCurrencyId.value = null
}

async function logout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="p-4 md:p-8">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">Профиль</h1>
      <BaseButton variant="danger" size="sm" @click="logout">Выйти</BaseButton>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Profile info -->
      <BaseCard>
        <h2 class="mb-4 font-semibold text-slate-800">Личные данные</h2>
        <form class="space-y-4" @submit.prevent="saveProfile">
          <div
            :class="[
              'rounded-lg px-4 py-3 text-sm border transition-opacity duration-200',
              saveSuccess
                ? 'bg-green-50 text-green-700 border-green-200 opacity-100'
                : 'opacity-0 border-transparent pointer-events-none',
            ]"
          >
            Сохранено
          </div>
          <BaseInput v-model="name" label="Имя" />
          <BaseInput v-model="email" label="Email" type="email" disabled />
          <BaseButton type="submit" :loading="authStore.loading">Сохранить</BaseButton>
        </form>
      </BaseCard>

      <!-- Notifications -->
      <BaseCard>
        <h2 class="mb-4 font-semibold text-slate-800">Уведомления</h2>
        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
          <div>
            <p class="font-medium text-slate-700">Email-уведомления</p>
            <p class="text-sm text-slate-500">Оповещать при изменении курса &gt;5%</p>
          </div>
          <button
            :class="[
              'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none',
              authStore.user?.notification_enabled ? 'bg-primary-600' : 'bg-slate-300',
            ]"
            @click="toggleNotifications"
          >
            <span
              :class="[
                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200',
                authStore.user?.notification_enabled ? 'translate-x-5' : 'translate-x-0',
              ]"
            />
          </button>
        </div>

        <!-- Subscriptions -->
        <div class="mt-6">
          <h3 class="mb-3 text-sm font-semibold text-slate-700">Подписки</h3>

          <div v-if="notifStore.subscriptions.length > 0" class="mb-4 space-y-2">
            <div
              v-for="sub in notifStore.subscriptions"
              :key="sub.id"
              class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2"
            >
              <div class="flex items-center gap-2 text-sm">
                <BaseBadge v-if="sub.bank">{{ sub.bank.name }}</BaseBadge>
                <BaseBadge v-if="sub.currency" variant="info">{{ sub.currency.code }}</BaseBadge>
                <span v-if="!sub.bank && !sub.currency" class="text-slate-500">Все изменения</span>
              </div>
              <button
                class="text-slate-400 hover:text-red-500 transition-colors"
                @click="notifStore.unsubscribe(sub.id)"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Add subscription -->
          <div class="space-y-2 rounded-lg bg-slate-50 p-3">
            <p class="text-xs font-medium text-slate-600 mb-2">Добавить подписку</p>
            <BaseSelect
              v-model="newSubBankId"
              :options="bankOptions"
              placeholder="Банк (необязательно)"
            />
            <BaseSelect
              v-model="newSubCurrencyId"
              :options="currencyOptions"
              placeholder="Валюта (необязательно)"
            />
            <BaseButton
              size="sm"
              :loading="notifStore.loading"
              :disabled="!newSubBankId && !newSubCurrencyId"
              @click="addSubscription"
            >
              Добавить
            </BaseButton>
          </div>
        </div>
      </BaseCard>
    </div>
  </div>
</template>
