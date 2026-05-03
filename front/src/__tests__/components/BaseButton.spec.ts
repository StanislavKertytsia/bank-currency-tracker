import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseButton from '@/components/base/BaseButton.vue'

describe('BaseButton', () => {
  it('renders slot content', () => {
    const wrapper = mount(BaseButton, {
      slots: { default: 'Click me' },
    })
    expect(wrapper.text()).toContain('Click me')
  })

  it('has primary variant classes by default', () => {
    const wrapper = mount(BaseButton)
    expect(wrapper.classes().join(' ')).toContain('bg-primary-600')
  })

  it('variant="danger" applies bg-red-600 class', () => {
    const wrapper = mount(BaseButton, {
      props: { variant: 'danger' },
    })
    expect(wrapper.classes().join(' ')).toContain('bg-red-600')
  })

  it('variant="ghost" applies text-slate-600 class', () => {
    const wrapper = mount(BaseButton, {
      props: { variant: 'ghost' },
    })
    expect(wrapper.classes().join(' ')).toContain('text-slate-600')
  })

  it('size="sm" applies px-3 class', () => {
    const wrapper = mount(BaseButton, {
      props: { size: 'sm' },
    })
    expect(wrapper.classes().join(' ')).toContain('px-3')
  })

  it('size="lg" applies px-5 class', () => {
    const wrapper = mount(BaseButton, {
      props: { size: 'lg' },
    })
    expect(wrapper.classes().join(' ')).toContain('px-5')
  })

  it('loading=true shows SVG spinner and button is disabled', () => {
    const wrapper = mount(BaseButton, {
      props: { loading: true },
    })
    expect(wrapper.find('svg').exists()).toBe(true)
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('loading=false does not show spinner', () => {
    const wrapper = mount(BaseButton, {
      props: { loading: false },
    })
    expect(wrapper.find('svg').exists()).toBe(false)
  })

  it('disabled=true makes button disabled', () => {
    const wrapper = mount(BaseButton, {
      props: { disabled: true },
    })
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('type="submit" sets attribute type="submit"', () => {
    const wrapper = mount(BaseButton, {
      props: { type: 'submit' },
    })
    expect(wrapper.attributes('type')).toBe('submit')
  })

  it('emits click event on click', async () => {
    const wrapper = mount(BaseButton)
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeTruthy()
  })
})
