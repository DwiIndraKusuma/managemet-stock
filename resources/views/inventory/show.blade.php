@extends('layouts.app')

@section('title', 'Stock Movements')
@section('page-title', 'Stock Movement History')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div id="movements-details" class="space-y-6">
            <p class="text-center text-gray-500">Loading...</p>
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
function waitForAPI(callback, maxAttempts = 50) {
    let attempts = 0;
    const interval = setInterval(() => {
        if (window.api) {
            clearInterval(interval);
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.error('API not available after multiple attempts.');
            const container = document.getElementById('movements-details');
            if (container) {
                container.innerHTML = '<p class="text-center text-red-500">Error: API service not available</p>';
            }
        }
        attempts++;
    }, 100);
}

const inventoryId = {{ $id }};

async function loadMovements() {
    const container = document.getElementById('movements-details');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get(`/inventory/${inventoryId}/movements`);
        console.log('Movements response:', response.data);
        
        // Handle response structure
        let movements = [];
        if (response.data?.data) {
            if (response.data.data.data && Array.isArray(response.data.data.data)) {
                movements = response.data.data.data; // Paginated response
            } else if (Array.isArray(response.data.data)) {
                movements = response.data.data;
            }
        }
        
        if (movements && movements.length > 0) {
            container.innerHTML = `
                <div class="mb-4">
                    <a href="/inventory" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Inventory</a>
                </div>
                <h2 class="text-xl font-bold mb-4">Stock Movements</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${movements.map(mov => `
                            <tr>
                                <td class="px-4 py-3 text-sm">${escapeHtml(new Date(mov.created_at).toLocaleString())}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs rounded ${
                                        mov.movement_type === 'in' ? 'bg-green-100 text-green-800' :
                                        mov.movement_type === 'out' ? 'bg-red-100 text-red-800' :
                                        'bg-gray-100 text-gray-800'
                                    }">
                                        ${escapeHtml(mov.movement_type || '-')}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">${mov.quantity || 0}</td>
                                <td class="px-4 py-3 text-sm">${escapeHtml(mov.notes || '-')}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } else {
            container.innerHTML = `
                <div class="mb-4">
                    <a href="/inventory" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Inventory</a>
                </div>
                <h2 class="text-xl font-bold mb-4">Stock Movements</h2>
                <p class="text-center text-gray-500">No movements found</p>
            `;
        }
    } catch (error) {
        console.error('Error loading movements:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load movements';
        container.innerHTML = `
            <div class="mb-4">
                <a href="/inventory" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Inventory</a>
            </div>
            <h2 class="text-xl font-bold mb-4">Stock Movements</h2>
            <p class="text-center text-red-500">Error: ${escapeHtml(errorMsg)}</p>
        `;
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

// Wait for API then load movements
waitForAPI(() => {
    loadMovements();
});
</script>
@endsection
