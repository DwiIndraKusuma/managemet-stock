@extends('layouts.app')

@section('title', 'Stock Opnames')
@section('page-title', 'Stock Opnames')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Stock Opnames</h1>
        </div>
        <a id="create-opname-btn" href="/stock-opnames/create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + Create Opname
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opname Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="opnames-table-body" class="bg-white divide-y divide-gray-200">
                <tr><td colspan="4" class="px-6 py-4 text-center">Loading...</td></tr>
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
            const tbody = document.getElementById('opnames-table-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error: API service not available</td></tr>';
            }
        }
        attempts++;
    }, 100);
}

async function loadOpnames() {
    const tbody = document.getElementById('opnames-table-body');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get('/stock-opnames?per_page=15');
        console.log('Opnames response:', response.data);
        
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
            tbody.innerHTML = data.data.map(opn => {
                const date = opn.opname_date ? new Date(opn.opname_date).toLocaleDateString() : '-';
                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${escapeHtml(opn.opname_number || '-')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${escapeHtml(date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${
                                opn.status === 'adjusted' ? 'bg-purple-100 text-purple-800' :
                                opn.status === 'approved' ? 'bg-green-100 text-green-800' :
                                opn.status === 'submitted' ? 'bg-blue-100 text-blue-800' :
                                opn.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                            }">${escapeHtml(opn.status || '-')}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="/stock-opnames/${opn.id}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No opnames found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading opnames:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load opnames';
        tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error: ${escapeHtml(errorMsg)}</td></tr>`;
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

waitForAPI(() => {
    loadOpnames();
});
</script>
@endsection
