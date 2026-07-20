<?= $this->extend('_layouts/main') ?>

<?= $this->section('content') ?>
<div x-data="{ ...detailFetcher('/api/users/<?= esc($id) ?>'), editMode: false, user: { username: '', email: '' } }"
     x-init="init().then(() => { user.username = data.username; user.email = data.email })">

    <?= $this->include('_partials/page_header', [
        'title'       => 'Detail User',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Users', 'url' => '/users'],
            ['label' => 'Detail']
        ]
    ]) ?>

    <!-- Loading -->
    <div x-show="loading" class="mt-6 text-sm text-gray-400">Memuat data...</div>

    <div x-show="!loading" class="mt-6 space-y-4" style="display: none;">

        <!-- View Mode -->
        <div x-show="!editMode" class="bg-white rounded-lg border border-gray-200 p-6 relative">
            <button @click="editMode = true"
                class="absolute top-5 right-5 text-sm font-medium text-gray-500 hover:text-gray-900 underline underline-offset-2">
                Edit
            </button>
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Informasi User</h3>
            <dl class="space-y-3">
                <div class="grid grid-cols-3 gap-4">
                    <dt class="text-sm text-gray-500">Username</dt>
                    <dd class="text-sm text-gray-900 col-span-2" x-text="data.username || '—'"></dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="text-sm text-gray-500">Email</dt>
                    <dd class="text-sm text-gray-900 col-span-2" x-text="data.email || '—'"></dd>
                </div>
            </dl>

            <!-- Danger zone -->
            <div class="mt-6 pt-4 border-t border-gray-100" x-data="confirmDialog()">
                <button type="button"
                    @click="open('Apakah Anda yakin ingin menghapus user ini?', async () => {
                        try {
                            await api.delete('/api/users/<?= esc($id) ?>');
                            window.location.href = '/users';
                        } catch (e) {
                            errorHandler.catch(e);
                        }
                    })"
                    class="text-sm text-red-600 hover:text-red-800 font-medium focus:outline-none">
                    Hapus User
                </button>
                <?= $this->include('_partials/confirm_dialog') ?>
            </div>
        </div>

        <!-- Edit Mode -->
        <div x-show="editMode" class="bg-white rounded-lg border border-gray-200 p-6" style="display: none;">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Edit User</h3>
            <form x-data="formHandler('/api/users/<?= esc($id) ?>', 'PUT')"
                  @submit.prevent="submit(user).then(() => {
                      if (Object.keys(errors).length === 0) {
                          data.username = user.username;
                          data.email = user.email;
                          editMode = false;
                          errorHandler.show('User berhasil diupdate', 'info');
                      }
                  })">
                <div class="space-y-5 max-w-md">
                    <div>
                        <label for="edit_username" class="block mb-1.5 text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="edit_username" x-model="user.username"
                            :class="errors.username ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-gray-400'"
                            class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
                            required>
                        <span x-show="errors.username" x-text="errors.username" class="mt-1 text-xs text-red-600 block"></span>
                    </div>
                    <div>
                        <label for="edit_email" class="block mb-1.5 text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="edit_email" x-model="user.email"
                            :class="errors.email ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-gray-400'"
                            class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
                            required>
                        <span x-show="errors.email" x-text="errors.email" class="mt-1 text-xs text-red-600 block"></span>
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <button type="submit" :disabled="isSubmitting"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="isSubmitting">Menyimpan...</span>
                            <span x-show="!isSubmitting">Simpan</span>
                        </button>
                        <button type="button"
                            @click="editMode = false; user.username = data.username; user.email = data.email"
                            class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Batal
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
<?= $this->endSection() ?>
