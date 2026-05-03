<script setup lang="ts">
import AppSidebar from './AppSidebar.vue'
import AppBottomNav from './AppBottomNav.vue'
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <AppSidebar />
    <main class="md:pl-64">
      <div class="min-h-screen pb-20 md:pb-0">
        <router-view />
      </div>
    </main>
    <AppBottomNav />
  </div>
</template>
