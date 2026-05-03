import { createApp } from 'vue'
import { createPinia } from 'pinia'
import VueApexCharts from 'vue3-apexcharts'

import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(VueApexCharts)

app.mount('#app')

const loader = document.getElementById('app-loader')
if (loader) {
  loader.classList.add('hidden')
  loader.addEventListener('transitionend', () => loader.remove(), { once: true })
}
