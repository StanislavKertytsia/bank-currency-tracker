<script setup lang="ts">
import { ref, computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { Statistic } from '@/types'

interface Props {
  data: Statistic[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

type SeriesMode = 'both' | 'buy' | 'sell'
const mode = ref<SeriesMode>('both')

const CURRENCY_PALETTE: Record<string, { buy: string; sell: string }> = {
  USD: { buy: '#22c55e', sell: '#f87171' },
  EUR: { buy: '#3b82f6', sell: '#fb923c' },
  GBP: { buy: '#8b5cf6', sell: '#ec4899' },
  CHF: { buy: '#06b6d4', sell: '#f59e0b' },
  PLN: { buy: '#10b981', sell: '#6366f1' },
}

const FALLBACK_BUY = ['#22c55e', '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981']
const FALLBACK_SELL = ['#f87171', '#fb923c', '#ec4899', '#f59e0b', '#6366f1']

interface Group {
  currencyId: number
  currencyCode: string
  points: Statistic[]
}

// Group by currency_id — chart is always single-bank
const groups = computed<Group[]>(() => {
  const map = new Map<number, Group>()
  for (const d of props.data) {
    const id = d.currency_id ?? 0
    if (!map.has(id)) {
      map.set(id, { currencyId: id, currencyCode: d.currency_code ?? String(id), points: [] })
    }
    map.get(id)!.points.push(d)
  }
  return [...map.values()]
})

// Union of all dates across all currency groups — ensures every series
// has a point at every x so the shared tooltip captures all series
const allTimestamps = computed(() => {
  const ts = new Set<number>()
  for (const g of groups.value) {
    for (const d of g.points) ts.add(new Date(d.recorded_at).getTime())
  }
  return [...ts].sort((a, b) => a - b)
})

const series = computed(() => {
  const result: { name: string; color: string; data: [number, number | null][] }[] = []

  groups.value.forEach((group, idx) => {
    const pal = CURRENCY_PALETTE[group.currencyCode]
    const buyColor = pal?.buy ?? FALLBACK_BUY[idx % FALLBACK_BUY.length]
    const sellColor = pal?.sell ?? FALLBACK_SELL[idx % FALLBACK_SELL.length]

    const byTs = new Map(group.points.map((d) => [new Date(d.recorded_at).getTime(), d]))

    // Forward-fill: carry the last known value instead of null so lines
    // stay continuous and the shared tooltip captures every series
    const fill = (getValue: (d: Statistic) => number): [number, number | null][] => {
      let last: number | null = null
      return allTimestamps.value.map((ts) => {
        const point = byTs.get(ts)
        if (point) last = getValue(point)
        return [ts, last]
      })
    }

    if (mode.value !== 'sell') {
      result.push({ name: `${group.currencyCode} — Покупка`, color: buyColor, data: fill((d) => d.buy_rate) })
    }
    if (mode.value !== 'buy') {
      result.push({ name: `${group.currencyCode} — Продажа`, color: sellColor, data: fill((d) => d.sell_rate) })
    }
  })

  return result
})

const chartOptions = computed(() => ({
  chart: {
    type: 'line' as const,
    height: 380,
    toolbar: {
      show: true,
      tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true },
    },
    zoom: { enabled: true, type: 'x' as const },
    animations: { enabled: false },
    fontFamily: 'inherit',
  },
  stroke: { width: 2, curve: 'straight' as const },
  colors: series.value.map((s) => s.color),
  xaxis: {
    type: 'datetime' as const,
    labels: {
      datetimeUTC: false,
      format: 'dd.MM',
      style: { fontSize: '11px', colors: '#64748b' },
    },
    crosshairs: { show: true, width: 1, position: 'back' as const },
    tooltip: { enabled: false },
  },
  yaxis: {
    labels: {
      formatter: (val: number) => val.toFixed(2),
      style: { fontSize: '11px', colors: '#64748b' },
    },
    decimalsInFloat: 4,
  },
  tooltip: {
    x: { format: 'dd.MM.yyyy' },
    y: {
      formatter: (val: number | null) => (val != null ? `${val.toFixed(4)} грн` : undefined),
    },
    shared: true,
    intersect: false,
  },
  legend: { show: false },
  grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
  markers: {
    size: 0,
    hover: { size: 5, sizeOffset: 3 },
  },
  dataLabels: { enabled: false },
  noData: { text: 'Нет данных', style: { color: '#94a3b8' } },
}))

const modeButtons: { key: SeriesMode; label: string }[] = [
  { key: 'both', label: 'Оба' },
  { key: 'buy', label: 'Покупка' },
  { key: 'sell', label: 'Продажа' },
]
</script>

<template>
  <div>
    <!-- Series legend (manual, replaces built-in) -->
    <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-2">
      <div class="flex overflow-hidden rounded-lg border border-slate-200">
        <button
          v-for="btn in modeButtons"
          :key="btn.key"
          :class="[
            'px-3 py-1.5 text-xs font-medium transition-colors',
            mode === btn.key
              ? 'bg-primary-600 text-white'
              : 'bg-white text-slate-600 hover:bg-slate-50',
          ]"
          @click="mode = btn.key"
        >
          {{ btn.label }}
        </button>
      </div>

      <!-- Currency color indicators -->
      <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
        <div
          v-for="g in groups"
          :key="g.currencyId"
          class="flex items-center gap-1.5"
        >
          <span
            class="inline-block h-2.5 w-2.5 rounded-full"
            :style="{ background: CURRENCY_PALETTE[g.currencyCode]?.buy ?? '#64748b' }"
          />
          <span class="text-xs font-medium text-slate-600">{{ g.currencyCode }}</span>
        </div>
      </div>

      <span v-if="data.length > 0" class="ml-auto text-xs text-slate-400">
        {{ allTimestamps.length }} дней
      </span>
    </div>

    <div v-if="loading" class="flex h-[380px] items-center justify-center">
      <div class="flex flex-col items-center gap-3">
        <svg class="h-8 w-8 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span class="text-sm text-slate-500">Загрузка данных...</span>
      </div>
    </div>

    <div
      v-else-if="data.length === 0"
      class="flex h-[380px] flex-col items-center justify-center gap-3 text-slate-400"
    >
      <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="1.5"
          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
        />
      </svg>
      <p class="text-sm">Нет данных за выбранный период</p>
      <p class="text-xs">Измените диапазон дат или фильтры</p>
    </div>

    <VueApexCharts
      v-else
      type="line"
      height="380"
      :options="chartOptions"
      :series="series"
    />
  </div>
</template>
