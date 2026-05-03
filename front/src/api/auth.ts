import api from './index'
import type { User } from '@/types'

interface LoginPayload {
  email: string
  password: string
}

interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

interface AuthResponse {
  token: string
  user: User
}

interface BackendAuthResponse {
  data: User
  token: string
}

export const login = async (payload: LoginPayload): Promise<AuthResponse> => {
  const { data } = await api.post<BackendAuthResponse>('/auth/login', payload)
  return { user: data.data, token: data.token }
}

export const register = async (payload: RegisterPayload): Promise<AuthResponse> => {
  const { data } = await api.post<BackendAuthResponse>('/auth/register', payload)
  return { user: data.data, token: data.token }
}

export const logout = async (): Promise<void> => {
  await api.post('/auth/logout')
}

export const getProfile = async (): Promise<User> => {
  const { data } = await api.get<{ data: User }>('/profile')
  return data.data
}

export const updateProfile = async (payload: Partial<User>): Promise<User> => {
  const { data } = await api.put<{ data: User }>('/profile', payload)
  return data.data
}

export const toggleNotifications = async (enabled: boolean): Promise<User> => {
  const { data } = await api.put<{ data: User }>('/profile/notifications', {
    notification_enabled: enabled,
  })
  return data.data
}
