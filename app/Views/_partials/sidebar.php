<?php
$currentPath = service('uri')->getPath();

/**
 * Returns Tailwind active class string if the given path matches current URL.
 */
$isActive = function (string $path) use ($currentPath): string {
    return $currentPath === ltrim($path, '/') || $currentPath === trim($path, '/')
        ? 'bg-blue-700 text-white'
        : 'text-gray-300 hover:bg-gray-700 hover:text-white';
};
?>
<aside class="w-64 flex-shrink-0 bg-white border-r border-gray-200 min-h-screen flex flex-col">

  <!-- Logo / App Name -->
  <div class="flex items-center h-16 px-6 border-b border-gray-200">
    <a href="/" class="text-gray-900 font-bold text-xl tracking-tight">
      <?= esc(env('APP_NAME', 'CI4 Kit')) ?>
    </a>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

    <!-- Dashboard -->
    <a href="/dashboard"
       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $isActive('/dashboard') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
      </svg>
      <span>Dashboard</span>
    </a>

    <!-- Users -->
    <a href="/users"
       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $isActive('/users') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
      </svg>
      <span>Pengguna</span>
    </a>

  </nav>

  <!-- Footer / Version -->
  <div class="px-6 py-4 border-t border-gray-200">
    <span class="text-xs text-gray-400">v2.0.0</span>
  </div>

</aside>
