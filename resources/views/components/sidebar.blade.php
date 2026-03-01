@php
    $currentRoute = request()->route()->getName() ?? '';
@endphp

<aside class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="p-4 border-b border-gray-700">
        <h1 class="text-xl font-bold">Inventory System</h1>
    </div>
    
    <nav class="flex-1 overflow-y-auto p-4">
        <ul class="space-y-2">
            <li>
                <a href="/dashboard" class="flex items-center px-4 py-2 rounded-lg {{ $currentRoute === 'dashboard' ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
            </li>
            
            <li class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase">Master Data</p>
            </li>
            
            <li>
                <a href="/items" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'items') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Items
                </a>
            </li>
            
            <li>
                <a href="/categories" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'categories') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Categories
                </a>
            </li>
            
            <li>
                <a href="/units" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'units') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Units
                </a>
            </li>
            
            <li class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase">Transactions</p>
            </li>
            
            <li>
                <a href="/requests" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'requests') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Requests
                </a>
            </li>
            
            <li id="po-menu-item">
                <a href="/purchase-orders" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'purchase-orders') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Purchase Orders
                </a>
            </li>
            
            <li id="receivings-menu-item" class="hidden">
                <a href="/receivings" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'receivings') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Receivings
                </a>
            </li>
            
            <li id="inventory-section" class="hidden pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase">Inventory</p>
            </li>
            
            <li id="inventory-menu-item" class="hidden">
                <a href="/inventory" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'inventory') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Stock List
                </a>
            </li>
            
            <li id="stock-opname-menu-item">
                <a href="/stock-opnames" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'stock-opnames') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Stock Opname
                </a>
            </li>
            
            <li id="users-menu-item" class="hidden pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase">Administration</p>
            </li>
            
            <li id="users-menu-link" class="hidden">
                <a href="/users" class="flex items-center px-4 py-2 rounded-lg {{ str_contains($currentRoute, 'users') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Users
                </a>
            </li>
        </ul>
    </nav>
</aside>
