<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import type { Branch } from '@/types'
import L from 'leaflet'
import 'leaflet.markercluster'
import 'leaflet/dist/leaflet.css'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'

interface Props {
  branches: Branch[]
  height?: string
}

const props = withDefaults(defineProps<Props>(), {
  height: '400px',
})

const mapEl = ref<HTMLElement | null>(null)
let map: L.Map | null = null
let clusterGroup: L.MarkerClusterGroup | null = null

function esc(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}

function renderMarkers(): void {
  if (!clusterGroup) return
  clusterGroup.clearLayers()
  if (props.branches.length === 0) return

  const icon = L.divIcon({
    html: '<div class="custom-marker"></div>',
    iconSize: [28, 28],
    iconAnchor: [14, 28],
    popupAnchor: [0, -28],
    className: '',
  })

  props.branches.forEach((branch) => {
    if (branch.lat == null || branch.lng == null) return
    if (!clusterGroup) return

    const marker = L.marker([branch.lat, branch.lng], { icon })
    marker.bindPopup(`
      <div style="min-width:180px">
        <strong style="font-size:13px">${esc(branch.branch_name)}</strong>
        <p style="margin:4px 0;font-size:12px;color:#64748b">${esc(branch.address)}</p>
        ${branch.phone ? `<p style="margin:0;font-size:12px">📞 ${esc(branch.phone)}</p>` : ''}
        ${branch.distance != null ? `<p style="margin:4px 0 0;font-size:11px;color:#6366f1">${(branch.distance / 1000).toFixed(1)} км</p>` : ''}
      </div>
    `)
    clusterGroup.addLayer(marker)
  })

  if (clusterGroup && map) {
    const bounds = clusterGroup.getBounds()
    if (bounds.isValid()) {
      map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 })
    }
  }
}

function initMap(): void {
  if (!mapEl.value) return

  map = L.map(mapEl.value, {
    center: [49.0, 32.0],
    zoom: 6,
    zoomControl: true,
  })

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(map)

  clusterGroup = L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 60 })
  map.addLayer(clusterGroup)
  renderMarkers()
}

watch(() => props.branches, renderMarkers, { deep: true })

onMounted(() => { initMap() })

onUnmounted(() => {
  map?.remove()
  map = null
  clusterGroup = null
})
</script>

<template>
  <div :style="{ height: props.height }" class="relative rounded-xl overflow-hidden border border-slate-200">
    <div ref="mapEl" class="h-full w-full" />
    <div
      v-if="branches.length === 0"
      class="absolute inset-0 flex items-center justify-center bg-slate-50/80"
    >
      <p class="text-slate-500 text-sm">Отделения не найдены</p>
    </div>
  </div>
</template>
