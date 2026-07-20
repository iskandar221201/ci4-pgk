<?= $this->extend('_layouts/main') ?>

<?= $this->section('content') ?>
<div x-data="dataTable('/api/users')">
    <?= $this->include('_partials/page_header', [
        'title'       => 'Daftar Users',
        'breadcrumbs' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Users']],
        'action'      => ['label' => 'Tambah User', 'url' => '/users/create'],
    ]) ?>

    <div class="mb-4">
        <?= $this->include('_partials/search_bar', ['placeholder' => 'Cari nama atau email...']) ?>
    </div>

    <!-- Loading skeleton -->
    <div x-show="loading" class="space-y-3 mt-4">
        <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="h-10 bg-gray-100 rounded-lg animate-pulse"></div>
        <?php endfor ?>
    </div>

    <!-- Empty state -->
    <div x-show="!loading && data.length === 0" class="mt-4">
        <?= $this->include('_partials/empty_state') ?>
    </div>

    <!-- Table -->
    <div x-show="!loading && data.length > 0" class="mt-4 overflow-x-auto rounded-lg border border-gray-200" style="display: none;">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Nama</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Email</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <template x-for="user in data" :key="user.id">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap" x-text="user.username || '-'"></td>
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap" x-text="user.email || '-'"></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a :href="'/users/' + user.id" class="text-sm font-medium text-gray-700 underline underline-offset-2 hover:text-gray-900">Detail</a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div x-show="!loading && data.length > 0" class="flex items-center justify-between mt-4 text-sm text-gray-500" style="display: none;">
        <span>Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span></span>
        <div class="flex items-center gap-2">
            <button type="button" @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
                class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-sm hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none">
                &larr; Sebelumnya
            </button>
            <button type="button" @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
                class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-sm hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none">
                Berikutnya &rarr;
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
