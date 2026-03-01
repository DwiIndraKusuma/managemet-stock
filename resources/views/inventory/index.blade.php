@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Stock Inventory')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inventory</h1>
        <p class="text-gray-600">Current stock levels</p>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <input type="text" id="search-input" placeholder="Search items..." 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reserved</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">In Transit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="inventory-table-body" class="bg-white divide-y divide-gray-200">
                <tr><td colspan="5" class="px-6 py-4 text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Wait for API to be available
function waitForAPI(callback, maxAttempts = 50) {
    let attempts = 0;
    const interval = setInterval(() => {
        if (window.api) {
            clearInterval(interval);
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.error('API not available after multiple attempts.');
            const tbody = document.getElementById('inventory-table-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: API service not available</td></tr>';
            }
        }
        attempts++;
    }, 100);
}

let searchTerm = '';

async function loadInventory() {
    const tbody = document.getElementById('inventory-table-body');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const params = new URLSearchParams({ per_page: 15 });
        if (searchTerm) params.append('search', searchTerm);

        const response = await window.api.get(`/inventory?${params}`);
        console.log('Inventory response:', response.data);
        
        // Handle different response structures
        let data = null;
        if (response.data?.data) {
            if (response.data.data.data) {
                data = response.data.data; // Paginated response
            } else if (Array.isArray(response.data.data)) {
                data = {
                    data: response.data.data,
                    current_page: 1,
                    last_page: 1
                };
            } else {
                data = response.data.data;
            }
        } else if (response.data) {
            data = response.data;
        }
        
        if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
            tbody.innerHTML = data.data.map(inv => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${escapeHtml(inv.item?.name || '-')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${inv.quantity_available || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${inv.quantity_reserved || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${inv.quantity_in_transit || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="/inventory/${inv.id}" class="text-indigo-600 hover:text-indigo-900">View Movements</a>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No inventory found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load inventory';
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: ${escapeHtml(errorMsg)}</td></tr>`;
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

waitForAPI(() => {
    document.getElementById('search-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            searchTerm = e.target.value;
            loadInventory();
        }
    });

    loadInventory();
});
</script>
@endsection
