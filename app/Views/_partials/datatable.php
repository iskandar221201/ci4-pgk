<?php
/**
 * Partial: datatable.php
 *
 * Note: this partial MUST be placed inside an Alpine x-data="dataTable(...)" scope.
 *
 * Accepted variables:
 * @var array $columns (required) [['key' => string, 'label' => string], ...]
 *
 * Action columns (edit, delete, etc.) are added by individual pages
 * via $this->section() inserted after including this partial.
 *
 * Usage example:
 *   <div x-data="dataTable('/api/users')">
 *     <?= $this->include('_partials/search_bar') ?>
 *     <?= $this->include('_partials/datatable', ['columns' => [
 *         ['key' => 'name',  'label' => 'Nama'],
 *         ['key' => 'email', 'label' => 'Email'],
 *     ]]) ?>
 *   </div>
 */

$columns = $columns ?? [];
?>

<!-- Loading skeleton -->
<div x-show="loading" class="space-y-3 mt-4">
  <?php for ($i = 0; $i < 5; $i++): ?>
    <div class="h-10 bg-gray-200 rounded-lg animate-pulse"></div>
  <?php endfor ?>
</div>

<!-- Empty state -->
<div x-show="!loading && data.length === 0" class="mt-4">
  <?= $this->include('_partials/empty_state') ?>
</div>

<!-- Table -->
<div x-show="!loading && data.length > 0" class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
  <table class="min-w-full divide-y divide-gray-200 text-sm">

    <thead class="bg-gray-50">
      <tr>
        <?php foreach ($columns as $col): ?>
          <th scope="col"
              class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">
            <?= esc($col['label'] ?? '') ?>
          </th>
        <?php endforeach ?>
        <!-- Slot for action column — pages may add <th> elements here via JS / template literals -->
      </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-100">
      <template x-for="row in data" :key="row.id ?? JSON.stringify(row)">
        <tr class="hover:bg-gray-50 transition-colors">
          <?php foreach ($columns as $col): ?>
            <td class="px-4 py-3 text-gray-700 whitespace-nowrap"
                x-text="row['<?= esc($col['key']) ?>'] ?? '-'">
            </td>
          <?php endforeach ?>
        </tr>
      </template>
    </tbody>

  </table>
</div>

<!-- Pagination -->
<div x-show="!loading && data.length > 0"
     class="flex items-center justify-between mt-4 text-sm text-gray-600">

  <!-- Page info -->
  <span>
    Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>
  </span>

  <!-- Navigation buttons -->
  <div class="flex items-center gap-2">
    <button type="button"
            @click="changePage(currentPage - 1)"
            :disabled="currentPage <= 1"
            class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none"
            aria-label="Previous page">
      &larr; Sebelumnya
    </button>

    <button type="button"
            @click="changePage(currentPage + 1)"
            :disabled="currentPage >= totalPages"
            class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none"
            aria-label="Next page">
      Berikutnya &rarr;
    </button>
  </div>

</div>
