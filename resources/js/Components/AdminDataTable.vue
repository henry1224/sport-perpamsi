<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
  paginator: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Array, default: () => [] },
  searchPlaceholder: { type: String, default: 'Cari data…' },
  itemLabel: { type: String, default: 'data' },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const perPage = ref(Number(props.filters.per_page || 10));
let timer;

const load = (page = 1) => router.get(window.location.pathname, {
  search: search.value || undefined,
  status: status.value || undefined,
  per_page: perPage.value,
  page,
}, { preserveState: true, preserveScroll: true, replace: true });

watch(search, () => {
  clearTimeout(timer);
  timer = setTimeout(() => load(), 300);
});
watch([status, perPage], () => load());
</script>

<template>
  <section class="admin-table-card">
    <header class="table-toolbar">
      <div v-if="$slots['toolbar-actions']" class="toolbar-actions">
        <span>Tampilan</span>
        <div><slot name="toolbar-actions" :rows="paginator.data" /></div>
      </div>
      <label class="search-field">
        <span>Pencarian</span>
        <input v-model="search" type="search" :placeholder="searchPlaceholder" />
      </label>
      <label v-if="filterOptions.length" class="filter-field">
        <span>Filter</span>
        <select v-model="status">
          <option value="">Semua Status</option>
          <option v-for="option in filterOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
      </label>
      <label class="per-page-field">
        <span>Per Halaman</span>
        <select v-model="perPage">
          <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">{{ size }}</option>
        </select>
      </label>
    </header>

    <div class="table-scroll">
      <slot :rows="paginator.data" />
    </div>

    <footer class="table-footer">
      <p>Menampilkan <b>{{ paginator.from || 0 }}–{{ paginator.to || 0 }}</b> dari <b>{{ paginator.total }}</b> {{ itemLabel }}</p>
      <nav v-if="paginator.last_page > 1" aria-label="Pagination">
        <button type="button" :disabled="paginator.current_page === 1" @click="load(paginator.current_page - 1)">Sebelumnya</button>
        <span>Halaman <b>{{ paginator.current_page }}</b> / {{ paginator.last_page }}</span>
        <button type="button" :disabled="paginator.current_page === paginator.last_page" @click="load(paginator.current_page + 1)">Berikutnya</button>
      </nav>
    </footer>
  </section>
</template>

<style scoped>
.admin-table-card { overflow: hidden; color: var(--portal-text, #172535); background: var(--portal-surface, #fff); border: 1px solid var(--portal-border, #d9e3e9); border-radius: var(--portal-radius, 14px); box-shadow: var(--portal-shadow, 0 8px 24px rgba(25,53,76,.07)); }
.table-toolbar { display: grid; grid-template-columns: auto minmax(260px, 1fr) 220px 150px; gap: 14px; padding: 18px 20px; background: var(--portal-surface-soft, #f7f9fa); border-bottom: 1px solid var(--portal-border, #d9e3e9); }
label { display: grid; gap: 7px; }
label span, .toolbar-actions > span { color: #60717f; font-size: 10px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
.toolbar-actions { display: grid; gap: 7px; }.toolbar-actions > div { display: flex; align-items: center; gap: 6px; min-height: 42px; }.toolbar-actions :deep(button) { min-height: 36px; padding: 8px 10px; color: var(--portal-primary, #1946a3); background: #fff; border: 1px solid var(--portal-border-strong, #cbd8df); border-radius: 8px; font-size: 10px; font-weight: 800; cursor: pointer; white-space: nowrap; }.toolbar-actions :deep(button:hover) { background: #edf4ff; border-color: #9fb7d6; }
input, select { width: 100%; min-height: 42px; padding: 10px 12px; color: var(--portal-text, #172535); background: var(--portal-surface, #fff); border: 1px solid var(--portal-border-strong, #cbd8df); border-radius: var(--portal-control-radius, 8px); outline: none; transition: border-color .16s, box-shadow .16s; }
input:focus, select:focus { border-color: var(--portal-primary, #1946a3); box-shadow: 0 0 0 3px rgba(25,70,163,.1); }
.table-scroll { overflow-x: auto; }
:deep(table) { width: 100%; min-width: 880px; border-collapse: collapse; }
:deep(th) { padding: 13px 16px; color: #60717f; background: var(--portal-surface-soft, #f7f9fa); border-bottom: 1px solid var(--portal-border, #dbe5ea); font-size: 10px; font-weight: 800; letter-spacing: .1em; text-align: left; text-transform: uppercase; white-space: nowrap; }
:deep(td) { padding: 15px 16px; color: #334553; border-bottom: 1px solid #e5ecef; font-size: 13px; line-height: 1.45; vertical-align: middle; }
:deep(tbody tr) { transition: background .15s ease; }
:deep(tbody tr:hover) { background: #f8fbfd; }
:deep(tbody tr:last-child td) { border-bottom: 0; }
:deep(.primary-cell) { display: grid; gap: 3px; }
:deep(.primary-cell strong) { color: #102132; font-size: 14px; font-weight: 750; }
:deep(.primary-cell small) { color: #738390; }
:deep(.status-badge) { display: inline-flex; align-items: center; width: fit-content; min-height: 27px; padding: 5px 9px; color: #655000; background: #fff2c8; border: 1px solid #f2dda0; border-radius: 999px; font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
:deep(.status-badge.success) { color: #087365; background: #dff7f2; border-color: #b9e8df; }
:deep(.status-badge.danger) { color: #a13d24; background: #ffe7df; border-color: #ffd0c1; }
:deep(.status-badge.info) { color: #1946a3; background: #e7efff; border-color: #cad9fb; }
:deep(.row-actions) { display: flex; flex-wrap: wrap; gap: 7px; justify-content: flex-end; }
:deep(.row-actions button) { min-height: 34px; padding: 7px 10px; color: #1946a3; background: #fff; border: 1px solid #cbd8df; border-radius: 7px; font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; cursor: pointer; transition: .16s ease; }
:deep(.row-actions button.primary) { color: #071126; background: #36c2f0; border-color: #36c2f0; }
:deep(.row-actions button.danger) { color: #a13d24; background: #fff4f0; border-color: #ffd0c1; }
:deep(.row-actions button:disabled) { opacity: .5; cursor: not-allowed; }
:deep(.empty-row td) { padding: 48px 20px; color: #7a8994; text-align: center; }
.table-footer { display: flex; align-items: center; justify-content: space-between; gap: 18px; min-height: 64px; padding: 12px 20px; color: #60717f; background: var(--portal-surface-soft, #f8fafb); border-top: 1px solid var(--portal-border, #dbe5ea); font-size: 12px; }
.table-footer p { margin: 0; }
nav { display: flex; align-items: center; gap: 12px; }
nav button { min-height: 36px; padding: 8px 12px; color: var(--portal-primary, #1946a3); background: var(--portal-surface, #fff); border: 1px solid var(--portal-border-strong, #cbd8df); border-radius: var(--portal-control-radius, 8px); font-weight: 800; cursor: pointer; }
nav button:disabled { color: #9caab4; background: #edf2f4; cursor: not-allowed; }
@media (max-width: 800px) { .table-toolbar { grid-template-columns: 1fr; } .table-footer { align-items: stretch; flex-direction: column; } nav { justify-content: space-between; } }
</style>
