import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/api/auth', () => ({
  login: vi.fn(),
  register: vi.fn(),
  logout: vi.fn(),
  getProfile: vi.fn(),
  updateProfile: vi.fn(),
  toggleNotifications: vi.fn(),
}))

import * as authApi from '@/api/auth'

const mockUser = {
  id: 1,
  name: 'John Doe',
  email: 'john@example.com',
  notification_enabled: false,
}

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('isAuthenticated is false when no token', () => {
    const store = useAuthStore()
    expect(store.isAuthenticated).toBe(false)
  })

  it('isAuthenticated is true after successful login', async () => {
    vi.mocked(authApi.login).mockResolvedValueOnce({ token: 'fake-token', user: mockUser })

    const store = useAuthStore()
    await store.login('john@example.com', 'password123')

    expect(store.isAuthenticated).toBe(true)
  })

  it('login calls authApi.login, saves token and user, writes to localStorage', async () => {
    vi.mocked(authApi.login).mockResolvedValueOnce({ token: 'fake-token', user: mockUser })

    const store = useAuthStore()
    await store.login('john@example.com', 'password123')

    expect(authApi.login).toHaveBeenCalledWith({ email: 'john@example.com', password: 'password123' })
    expect(store.token).toBe('fake-token')
    expect(store.user).toEqual(mockUser)
    expect(localStorage.getItem('auth_token')).toBe('fake-token')
  })

  it('login sets error and rethrows on API error', async () => {
    const apiError = new Error('Invalid credentials')
    vi.mocked(authApi.login).mockRejectedValueOnce(apiError)

    const store = useAuthStore()

    await expect(store.login('john@example.com', 'wrongpassword')).rejects.toThrow()
    expect(store.error).toBeTruthy()
  })

  it('register calls authApi.register, saves token and user', async () => {
    vi.mocked(authApi.register).mockResolvedValueOnce({ token: 'new-token', user: mockUser })

    const store = useAuthStore()
    await store.register('John Doe', 'john@example.com', 'password123')

    expect(authApi.register).toHaveBeenCalledWith({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    })
    expect(store.token).toBe('new-token')
    expect(store.user).toEqual(mockUser)
  })

  it('logout calls authApi.logout, clears token/user/localStorage', async () => {
    vi.mocked(authApi.login).mockResolvedValueOnce({ token: 'fake-token', user: mockUser })
    vi.mocked(authApi.logout).mockResolvedValueOnce(undefined)

    const store = useAuthStore()
    await store.login('john@example.com', 'password123')
    await store.logout()

    expect(authApi.logout).toHaveBeenCalled()
    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(localStorage.getItem('auth_token')).toBeNull()
  })

  it('fetchProfile calls getProfile and updates user when token exists', async () => {
    vi.mocked(authApi.getProfile).mockResolvedValueOnce(mockUser)

    const store = useAuthStore()
    store.token = 'existing-token'
    await store.fetchProfile()

    expect(authApi.getProfile).toHaveBeenCalled()
    expect(store.user).toEqual(mockUser)
  })

  it('fetchProfile does not call API when no token', async () => {
    const store = useAuthStore()
    store.token = null
    await store.fetchProfile()

    expect(authApi.getProfile).not.toHaveBeenCalled()
  })

  it('toggleNotifications calls authApi.toggleNotifications', async () => {
    const updatedUser = { ...mockUser, notification_enabled: true }
    vi.mocked(authApi.toggleNotifications).mockResolvedValueOnce(updatedUser)

    const store = useAuthStore()
    await store.toggleNotifications(true)

    expect(authApi.toggleNotifications).toHaveBeenCalledWith(true)
    expect(store.user).toEqual(updatedUser)
  })
})
