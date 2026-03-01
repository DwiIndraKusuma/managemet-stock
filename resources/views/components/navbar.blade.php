<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600" data-user-name>User</span>
                    <span class="text-xs text-gray-400" data-user-role>Role</span>
                </div>
                <button id="logout-btn" class="px-4 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition">
                    Logout
                </button>
            </div>
        </div>
    </div>
</nav>
