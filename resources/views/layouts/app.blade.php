<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Inventory & Procurement System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            @include('components.navbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div id="alert-container"></div>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Modals -->
    <div id="modal-container"></div>

    <script>
    // Show/hide menu items based on role
    document.addEventListener('DOMContentLoaded', function() {
        // Get user from localStorage
        const userStr = localStorage.getItem('user');
        if (userStr) {
            try {
                const user = JSON.parse(userStr);
                const userRole = user.role?.name || user.role_name;
                
                // Show Users menu only for Admin Gudang
                if (userRole === 'admin_gudang') {
                    const usersMenuItem = document.getElementById('users-menu-item');
                    const usersMenuLink = document.getElementById('users-menu-link');
                    
                    if (usersMenuItem) {
                        usersMenuItem.classList.remove('hidden');
                    }
                    if (usersMenuLink) {
                        usersMenuLink.classList.remove('hidden');
                    }
                }
                
                // Hide Purchase Orders for Technician
                if (userRole === 'technician') {
                    const poMenuItem = document.getElementById('po-menu-item');
                    if (poMenuItem) {
                        poMenuItem.classList.add('hidden');
                    }
                }
                
                // Show Receivings only for Admin Gudang
                if (userRole === 'admin_gudang') {
                    const receivingsMenuItem = document.getElementById('receivings-menu-item');
                    if (receivingsMenuItem) {
                        receivingsMenuItem.classList.remove('hidden');
                    }
                }
                
                // Show Inventory section and menu only for Admin Gudang
                if (userRole === 'admin_gudang') {
                    const inventorySection = document.getElementById('inventory-section');
                    const inventoryMenuItem = document.getElementById('inventory-menu-item');
                    
                    if (inventorySection) {
                        inventorySection.classList.remove('hidden');
                    }
                    if (inventoryMenuItem) {
                        inventoryMenuItem.classList.remove('hidden');
                    }
                }
                
                // Hide Stock Opname for Technician
                if (userRole === 'technician') {
                    const stockOpnameMenuItem = document.getElementById('stock-opname-menu-item');
                    if (stockOpnameMenuItem) {
                        stockOpnameMenuItem.classList.add('hidden');
                    }
                }
                
                // Update navbar user info
                const userNameEl = document.querySelector('[data-user-name]');
                const userRoleEl = document.querySelector('[data-user-role]');
                
                if (userNameEl) {
                    userNameEl.textContent = user.name || 'User';
                }
                if (userRoleEl) {
                    userRoleEl.textContent = user.role?.display_name || user.role_name || 'Role';
                }
                
                // Hide Create buttons based on role
                // Master Data (Items, Categories, Units) - Only Admin Gudang
                if (userRole !== 'admin_gudang') {
                    const createItemBtn = document.getElementById('create-item-btn');
                    const createCategoryBtn = document.getElementById('create-category-btn');
                    const createUnitBtn = document.getElementById('create-unit-btn');
                    
                    if (createItemBtn) createItemBtn.style.display = 'none';
                    if (createCategoryBtn) createCategoryBtn.style.display = 'none';
                    if (createUnitBtn) createUnitBtn.style.display = 'none';
                }
                
                // Purchase Orders - Only Admin Gudang
                if (userRole !== 'admin_gudang') {
                    const createPoBtn = document.getElementById('create-po-btn');
                    if (createPoBtn) createPoBtn.style.display = 'none';
                }
                
                // Stock Opnames - Only Admin Gudang
                if (userRole !== 'admin_gudang') {
                    const createOpnameBtn = document.getElementById('create-opname-btn');
                    if (createOpnameBtn) createOpnameBtn.style.display = 'none';
                }
                
                // Requests - Hide for SPV (only Technician and Admin Gudang can create)
                if (userRole === 'spv') {
                    const createRequestBtn = document.getElementById('create-request-btn');
                    if (createRequestBtn) createRequestBtn.style.display = 'none';
                }
                
                // Receivings - Only Admin Gudang (already protected by route, but hide button)
                if (userRole !== 'admin_gudang') {
                    const createReceivingBtn = document.getElementById('create-receiving-btn');
                    if (createReceivingBtn) createReceivingBtn.style.display = 'none';
                }
            } catch (error) {
                console.error('Error parsing user data:', error);
            }
        }
    });
    </script>
</body>
</html>
