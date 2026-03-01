@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="stats-container">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-2xl font-semibold text-gray-900" id="total-items">-</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">My Requests</p>
                    <p class="text-2xl font-semibold text-gray-900" id="my-requests">-</p>
                </div>
            </div>
        </div>

        <div id="pending-po-card" class="bg-white rounded-lg shadow p-6 hidden">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending PO</p>
                    <p class="text-2xl font-semibold text-gray-900" id="pending-po">-</p>
                </div>
            </div>
        </div>

        <div id="low-stock-card" class="bg-white rounded-lg shadow p-6 hidden">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                    <p class="text-2xl font-semibold text-gray-900" id="low-stock">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="quick-actions-container">
            <a href="/requests/create" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <svg class="h-8 w-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">Create Request</p>
                    <p class="text-sm text-gray-500">Request new items</p>
                </div>
            </a>

            <a id="create-po-action" href="/purchase-orders/create" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition hidden">
                <svg class="h-8 w-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">Create PO</p>
                    <p class="text-sm text-gray-500">Create purchase order</p>
                </div>
            </a>

            <a id="stock-opname-action" href="/stock-opnames/create" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition hidden">
                <svg class="h-8 w-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-900">Stock Opname</p>
                    <p class="text-sm text-gray-500">Create stock opname</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Requests</h3>
        <div id="recent-requests" class="space-y-4">
            <p class="text-gray-500 text-center py-4">Loading...</p>
        </div>
    </div>
</div>

<script>
// Helper function to escape HTML
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Wait for API to be available
function waitForAPI(callback, maxAttempts = 100) {
    let attempts = 0;
    const interval = setInterval(() => {
        if (window.api) {
            clearInterval(interval);
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.error('API not available after multiple attempts.');
            document.getElementById('recent-requests').innerHTML = '<p class="text-red-500 text-center py-4">Error: API service not available</p>';
        }
        attempts++;
    }, 100);
}

// Get user role
function getUserRole() {
    try {
        const userStr = localStorage.getItem('user');
        if (userStr) {
            const user = JSON.parse(userStr);
            return user.role?.name || user.role_name || null;
        }
    } catch (error) {
        console.error('Error getting user role:', error);
    }
    return null;
}

async function loadDashboard() {
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const userRole = getUserRole();
        const isTechnician = userRole === 'technician';
        const isAdminOrSPV = userRole === 'admin_gudang' || userRole === 'spv';

        // Show/hide cards based on role
        if (isAdminOrSPV) {
            document.getElementById('pending-po-card')?.classList.remove('hidden');
            document.getElementById('low-stock-card')?.classList.remove('hidden');
            document.getElementById('create-po-action')?.classList.remove('hidden');
            document.getElementById('stock-opname-action')?.classList.remove('hidden');
        } else {
            document.getElementById('pending-po-card')?.classList.add('hidden');
            document.getElementById('low-stock-card')?.classList.add('hidden');
            document.getElementById('create-po-action')?.classList.add('hidden');
            document.getElementById('stock-opname-action')?.classList.add('hidden');
        }

        // Load statistics based on role
        const promises = [
            window.api.get('/items?per_page=1').catch(() => ({ data: { data: { total: 0 } } })),
            isTechnician 
                ? window.api.get('/requests?per_page=1').catch(() => ({ data: { data: { total: 0 } } }))
                : window.api.get('/requests?status=submitted&per_page=1').catch(() => ({ data: { data: { total: 0 } } }))
        ];

        if (isAdminOrSPV) {
            promises.push(
                window.api.get('/purchase-orders?status=pending_approval&per_page=1').catch(() => ({ data: { data: { total: 0 } } })),
                window.api.get('/inventory?per_page=1').catch(() => ({ data: { data: { total: 0 } } }))
            );
        }

        const results = await Promise.all(promises);
        const itemsRes = results[0];
        const requestsRes = results[1];

        // Update statistics
        const totalItems = itemsRes.data?.data?.total || itemsRes.data?.data?.data?.length || 0;
        const myRequests = requestsRes.data?.data?.total || requestsRes.data?.data?.data?.length || 0;

        document.getElementById('total-items').textContent = totalItems;
        document.getElementById('my-requests').textContent = myRequests;

        if (isAdminOrSPV) {
            const poRes = results[2];
            const inventoryRes = results[3];
            const pendingPO = poRes.data?.data?.total || poRes.data?.data?.data?.length || 0;
            const lowStock = inventoryRes.data?.data?.total || 0;
            document.getElementById('pending-po').textContent = pendingPO;
            document.getElementById('low-stock').textContent = lowStock;
        }

        // Load recent requests (filtered by role in backend)
        const recentRes = await window.api.get('/requests?per_page=5').catch(() => ({ data: { data: { data: [] } } }));
        const requests = recentRes.data?.data?.data || recentRes.data?.data || [];
        
        const container = document.getElementById('recent-requests');
        if (requests && requests.length > 0) {
            container.innerHTML = requests.map(req => `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">${escapeHtml(req.request_number || '-')}</p>
                        <p class="text-sm text-gray-500">${escapeHtml(req.user?.name || 'Unknown')}</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full ${
                        req.status === 'approved' ? 'bg-green-100 text-green-800' :
                        req.status === 'rejected' ? 'bg-red-100 text-red-800' :
                        'bg-yellow-100 text-yellow-800'
                    }">${escapeHtml(req.status || '-')}</span>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-gray-500 text-center py-4">No recent requests</p>';
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load dashboard data';
        
        // Handle 401 - redirect to login
        if (error.response?.status === 401) {
            if (window.showAlert) {
                window.showAlert('Session expired. Please login again.', 'error');
            }
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
            return;
        }

        // Don't show 403 errors in dashboard - just hide the cards
        if (error.response?.status === 403) {
            console.log('Access denied for some resources - hiding unavailable cards');
            return;
        }

        // Show error in recent requests section only for non-403 errors
        document.getElementById('recent-requests').innerHTML = `<p class="text-red-500 text-center py-4">Error: ${escapeHtml(errorMsg)}</p>`;
        
        if (window.showAlert && error.response?.status !== 403) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

// Wait for API then load dashboard
setTimeout(() => {
    waitForAPI(loadDashboard);
}, 100);
</script>
@endsection
