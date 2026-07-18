<script setup>
defineProps({ eyebrow: String, title: String, meta: String });
</script>

<template>
  <div class="section-title" :class="{ split: !!meta }">
    <div>
      <p>{{ eyebrow }}</p>
      <h2>{{ title }}</h2>
    </div>
    <span v-if="meta">{{ meta }}</span>
  </div>
</template>

<style scoped>
.section-title { margin-bottom: 18px; }
.section-title p { margin: 0; color: #36C2F0; font-size: 12px; font-weight: 950; letter-spacing: .16em; text-transform: uppercase; }
.section-title h2 { margin: 4px 0 0; font-size: 34px; letter-spacing: -.04em; }
.section-title.split { display: flex; justify-content: space-between; align-items: end; gap: 18px; }
.section-title span { color: #F6C64A; font-size: 12px; font-weight: 950; letter-spacing: .12em; text-transform: uppercase; }
</style>
