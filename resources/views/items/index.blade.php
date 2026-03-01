@extends('layouts.app')

@section('title', 'Items')
@section('page-title', 'Items Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Items</h1>
            <p class="text-gray-600">Manage your inventory items</p>
        </div>
        <a id="create-item-btn" href="/items/create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            + Add Item
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex gap-4">
            <input type="text" id="search-input" placeholder="Search items..." 
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Categories</option>
            </select>
            <button id="search-btn" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Search
            </button>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="items-table-body" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="flex justify-center"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let searchTerm = '';
    let categoryFilter = '';

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
                const tbody = document.getElementById('items-table-body');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: API service not available</td></tr>';
                }
            }
            attempts++;
        }, 100);
    }

    // Make loadItems available globally for pagination buttons
    window.loadItems = async function(page = 1) {
        const tbody = document.getElementById('items-table-body');
        
        try {
            if (!window.api) {
                throw new Error('API client not available');
            }

            const params = new URLSearchParams({
                page: page,
                per_page: 15
            });
            
            if (searchTerm) params.append('search', searchTerm);
            if (categoryFilter) params.append('category_id', categoryFilter);

            const response = await window.api.get(`/items?${params}`);
            console.log('Items response:', response.data);

            // Handle different response structures
            let data = null;
            if (response.data?.data) {
                // If response.data.data exists, it might be paginated
                if (response.data.data.data) {
                    data = response.data.data; // Paginated response
                } else if (Array.isArray(response.data.data)) {
                    // Direct array response
                    data = {
                        data: response.data.data,
                        current_page: 1,
                        last_page: 1,
                        total: response.data.data.length
                    };
                } else {
                    data = response.data.data; // Try as is
                }
            } else if (response.data) {
                // Fallback to response.data
                data = response.data;
            }

            // Render table
            if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
                tbody.innerHTML = data.data.map(item => {
                    // Escape HTML to prevent XSS
                    const escapeHtml = (text) => {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    };
                    
                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(item.code || '-')}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(item.name || '')}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(item.category?.name || '-')}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(item.unit?.name || '-')}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.inventory?.quantity_available || 0}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="/items/${item.id}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                ${getUserRole() === 'admin_gudang' ? `
                                    <a href="/items/${item.id}/edit" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                                    <button onclick="deleteItem(${item.id})" class="text-red-600 hover:text-red-900">Delete</button>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No items found</td></tr>';
            }

            // Render pagination
            if (data && (data.current_page || data.last_page)) {
                renderPagination(data);
            } else {
                document.getElementById('pagination').innerHTML = '';
            }
            
            currentPage = page;
        } catch (error) {
            console.error('Error loading items:', error);
            const errorMsg = error.response?.data?.message || error.message || 'Failed to load items';
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${errorMsg}</td></tr>`;
            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            }
        }
    }

    function renderPagination(data) {
        const pagination = document.getElementById('pagination');
        if (!data || data.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '<div class="flex gap-2">';
        
        // Previous button
        if (data.current_page > 1) {
            html += `<button onclick="window.loadItems(${data.current_page - 1})" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Previous</button>`;
        }

        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">${i}</button>`;
            } else {
                html += `<button onclick="window.loadItems(${i})" class="px-4 py-2 border rounded-lg hover:bg-gray-50">${i}</button>`;
            }
        }

        // Next button
        if (data.current_page < data.last_page) {
            html += `<button onclick="window.loadItems(${data.current_page + 1})" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Next</button>`;
        }

        html += '</div>';
        pagination.innerHTML = html;
    }

    window.deleteItem = async function(id) {
        if (!confirm('Are you sure you want to delete this item?')) return;
        
        try {
            await window.api.delete(`/items/${id}`);
            if (window.showAlert) {
                window.showAlert('Item deleted successfully', 'success');
            }
            window.loadItems(currentPage);
        } catch (error) {
            const errorMsg = error.response?.data?.message || 'Failed to delete item';
            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            }
        }
    };

    // Load categories for filter
    async function loadCategories() {
        try {
            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.get('/categories');
            console.log('Categories response:', response.data);
            
            const categories = response.data?.data || response.data || [];
            const select = document.getElementById('category-filter');
            
            if (Array.isArray(categories) && categories.length > 0) {
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name || '';
                    select.appendChild(option);
                });
            }
            
            select.addEventListener('change', (e) => {
                categoryFilter = e.target.value;
                window.loadItems(1);
            });
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Event listeners
    document.getElementById('search-btn').addEventListener('click', () => {
        searchTerm = document.getElementById('search-input').value;
        window.loadItems(1);
    });

    document.getElementById('search-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            searchTerm = e.target.value;
            window.loadItems(1);
        }
    });

    window.deleteItem = async function(id) {
        if (!confirm('Are you sure you want to delete this item?')) return;
        
        try {
            await window.api.delete(`/items/${id}`);
            if (window.showAlert) {
                window.showAlert('Item deleted successfully', 'success');
            }
            window.loadItems(currentPage);
        } catch (error) {
            const errorMsg = error.response?.data?.message || 'Failed to delete item';
            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            }
        }
    };

    // Helper function to get user role
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
    window.getUserRole = getUserRole;

    // Initial load
    waitForAPI(() => {
        window.loadItems();
        loadCategories();
    });
});
</script>
@endsection
