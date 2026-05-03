<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()

function isActive(to: string) {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 z-30 flex border-t border-slate-200 bg-white md:hidden">
    <RouterLink
      to="/"
      :class="[
        'flex flex-1 flex-col items-center gap-1 py-3 text-xs font-medium transition-colors',
        isActive('/') ? 'text-primary-600' : 'text-slate-500',
      ]"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
        />
      </svg>
      Главная
    </RouterLink>
    <RouterLink
      to="/banks"
      :class="[
        'flex flex-1 flex-col items-center gap-1 py-3 text-xs font-medium transition-colors',
        isActive('/banks') ? 'text-primary-600' : 'text-slate-500',
      ]"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
        />
      </svg>
      Банки
    </RouterLink>
    <RouterLink
      to="/statistics"
      :class="[
        'flex flex-1 flex-col items-center gap-1 py-3 text-xs font-medium transition-colors',
        isActive('/statistics') ? 'text-primary-600' : 'text-slate-500',
      ]"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
        />
      </svg>
      Статистика
    </RouterLink>
    <RouterLink
      :to="authStore.isAuthenticated ? '/profile' : '/login'"
      :class="[
        'flex flex-1 flex-col items-center gap-1 py-3 text-xs font-medium transition-colors',
        (isActive('/profile') || isActive('/login')) ? 'text-primary-600' : 'text-slate-500',
      ]"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
        />
      </svg>
      {{ authStore.isAuthenticated ? 'Профиль' : 'Войти' }}
    </RouterLink>
  </nav>
</template>
