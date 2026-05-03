import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import App from '../App.vue'

vi.mock('@/api/auth', () => ({
  login: vi.fn(),
  register: vi.fn(),
  logout: vi.fn(),
  getProfile: vi.fn().mockResolvedValue(null),
  updateProfile: vi.fn(),
  toggleNotifications: vi.fn(),
}))

vi.mock('vue-router', () => ({
  RouterView: { template: '<div />' },
  useRouter: vi.fn(),
  useRoute: vi.fn(),
}))

describe('App', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  it('mounts without errors', () => {
    const wrapper = mount(App, {
      global: { plugins: [createPinia()] },
    })
    expect(wrapper.exists()).toBe(true)
  })
})
