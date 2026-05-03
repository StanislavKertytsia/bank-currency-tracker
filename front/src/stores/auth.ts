import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types'
import * as authApi from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const res = await authApi.login({ email, password })
      token.value = res.token
      user.value = res.user
      localStorage.setItem('auth_token', res.token)
    } catch (e: unknown) {
      error.value = extractError(e)
      throw e
    } finally {
      loading.value = false
    }
  }

  async function register(name: string, email: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const res = await authApi.register({
        name,
        email,
        password,
        password_confirmation: password,
      })
      token.value = res.token
      user.value = res.user
      localStorage.setItem('auth_token', res.token)
    } catch (e: unknown) {
      error.value = extractError(e)
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
    }
  }

  async function fetchProfile() {
    if (!token.value) return
    try {
      user.value = await authApi.getProfile()
    } catch {
      // token may be invalid
    }
  }

  async function updateProfile(payload: Partial<User>) {
    loading.value = true
    error.value = null
    try {
      user.value = await authApi.updateProfile(payload)
    } catch (e: unknown) {
      error.value = extractError(e)
      throw e
    } finally {
      loading.value = false
    }
  }

  async function toggleNotifications(enabled: boolean) {
    user.value = await authApi.toggleNotifications(enabled)
  }

  function extractError(e: unknown): string {
    if (e && typeof e === 'object' && 'response' in e) {
      const err = e as { response?: { data?: { message?: string } } }
      return err.response?.data?.message ?? 'Произошла ошибка'
    }
    return 'Произошла ошибка'
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    login,
    register,
    logout,
    fetchProfile,
    updateProfile,
    toggleNotifications,
  }
})
