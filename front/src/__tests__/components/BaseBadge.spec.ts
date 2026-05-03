import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseBadge from '@/components/base/BaseBadge.vue'

describe('BaseBadge', () => {
  it('renders slot content', () => {
    const wrapper = mount(BaseBadge, {
      slots: { default: 'NBU' },
    })
    expect(wrapper.text()).toContain('NBU')
  })

  it('default variant applies bg-slate-100 class', () => {
    const wrapper = mount(BaseBadge)
    expect(wrapper.classes().join(' ')).toContain('bg-slate-100')
  })

  it('variant="success" applies bg-green-100 class', () => {
    const wrapper = mount(BaseBadge, {
      props: { variant: 'success' },
    })
    expect(wrapper.classes().join(' ')).toContain('bg-green-100')
  })

  it('variant="danger" applies bg-red-100 class', () => {
    const wrapper = mount(BaseBadge, {
      props: { variant: 'danger' },
    })
    expect(wrapper.classes().join(' ')).toContain('bg-red-100')
  })

  it('variant="warning" applies bg-amber-100 class', () => {
    const wrapper = mount(BaseBadge, {
      props: { variant: 'warning' },
    })
    expect(wrapper.classes().join(' ')).toContain('bg-amber-100')
  })

  it('variant="info" applies bg-blue-100 class', () => {
    const wrapper = mount(BaseBadge, {
      props: { variant: 'info' },
    })
    expect(wrapper.classes().join(' ')).toContain('bg-blue-100')
  })
})
