<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseCard from '@/components/base/BaseCard.vue'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')

async function submit() {
  await authStore.register(name.value, email.value, password.value)
  router.push('/')
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-50 p-4">
    <div class="w-full max-w-md">
      <div class="mb-8 flex flex-col items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-600">
          <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">MultiBank</h1>
        <p class="text-sm text-slate-500">Создайте аккаунт</p>
      </div>

      <BaseCard>
        <form class="space-y-4" @submit.prevent="submit">
          <div
            v-if="authStore.error"
            class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 border border-red-200"
          >
            {{ authStore.error }}
          </div>

          <BaseInput
            v-model="name"
            label="Имя"
            placeholder="Иван Петров"
          />
          <BaseInput
            v-model="email"
            label="Email"
            type="email"
            placeholder="your@email.com"
          />
          <BaseInput
            v-model="password"
            label="Пароль"
            type="password"
            placeholder="Минимум 8 символов"
          />

          <BaseButton
            type="submit"
            class="w-full"
            :loading="authStore.loading"
          >
            Зарегистрироваться
          </BaseButton>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500">
          Уже есть аккаунт?
          <RouterLink to="/login" class="font-medium text-primary-600 hover:text-primary-700">
            Войти
          </RouterLink>
        </p>
      </BaseCard>
    </div>
  </div>
</template>
