<?= $this->extend('_layouts/main') ?>

<?= $this->section('content') ?>
<?= $this->include('_partials/page_header', [
    'title'       => 'Tambah User',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Users', 'url' => '/users'],
        ['label' => 'Tambah']
    ]
]) ?>

<div class="mt-6 bg-white rounded-lg border border-gray-200 p-6 max-w-md">
    <form x-data="{ ...formHandler('/api/users', 'POST', '/users'), user: { username: '', email: '', password: '' } }" @submit.prevent="submit(user)">
        <div class="space-y-5">
            <div>
                <label for="username" class="block mb-1.5 text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" x-model="user.username"
                    :class="errors.username ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-gray-400'"
                    class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
                    required>
                <span x-show="errors.username" x-text="errors.username" class="mt-1 text-xs text-red-600 block"></span>
            </div>
            <div>
                <label for="email" class="block mb-1.5 text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" x-model="user.email"
                    :class="errors.email ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-gray-400'"
                    class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
                    required>
                <span x-show="errors.email" x-text="errors.email" class="mt-1 text-xs text-red-600 block"></span>
            </div>
            <div>
                <label for="password" class="block mb-1.5 text-sm font-medium text-gray-700">Password</label>
                <input type="text" id="password" x-model="user.password"
                    :class="errors.password ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-gray-400'"
                    class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition font-mono"
                    placeholder="Minimal 8 karakter"
                    required>
                <p class="mt-1 text-xs text-gray-400">Password akan diberikan ke user untuk login pertama kali.</p>
                <span x-show="errors.password" x-text="errors.password" class="mt-1 text-xs text-red-600 block"></span>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit" :disabled="isSubmitting"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting">Menyimpan...</span>
                    <span x-show="!isSubmitting">Simpan</span>
                </button>
                <a href="/users" class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</a>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
