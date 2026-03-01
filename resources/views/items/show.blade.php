@extends('layouts.app')

@section('title', 'Item Details')
@section('page-title', 'Item Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div id="item-details" class="space-y-6">
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
            const container = document.getElementById('item-details');
            if (container) {
                container.innerHTML = '<p class="text-center text-red-500">Error: API service not available</p>';
            }
        }
        attempts++;
    }, 100);
}

const itemId = {{ $id }};

async function loadItem() {
    const container = document.getElementById('item-details');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get(`/items/${itemId}`);
        console.log('Item response:', response.data);
        
        const item = response.data?.data || response.data;

        if (!item) {
            throw new Error('Item not found');
        }

        container.innerHTML = `
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">${escapeHtml(item.name || '')}</h2>
                    <p class="text-gray-600">${escapeHtml(item.code || '-')}</p>
                </div>
                <a href="/items/${item.id}/edit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Edit
                </a>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <p class="mt-1 text-gray-900">${escapeHtml(item.category?.name || '-')}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit</label>
                    <p class="mt-1 text-gray-900">${escapeHtml(item.unit?.name || '-')}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Minimum Stock</label>
                    <p class="mt-1 text-gray-900">${item.min_stock || 0}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Stock</label>
                    <p class="mt-1 text-gray-900">${item.inventory?.quantity_available || 0}</p>
                </div>
            </div>

            ${item.description ? `
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <p class="mt-1 text-gray-900 whitespace-pre-wrap">${escapeHtml(item.description)}</p>
            </div>
            ` : ''}
        `;
    } catch (error) {
        console.error('Error loading item:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load item details';
        container.innerHTML = `<p class="text-center text-red-500">Error: ${escapeHtml(errorMsg)}</p>`;
        
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
        
        if (error.response?.status === 404) {
            setTimeout(() => {
                window.location.href = '/items';
            }, 2000);
        }
    }
}

waitForAPI(() => {
    loadItem();
});
</script>
@endsection
