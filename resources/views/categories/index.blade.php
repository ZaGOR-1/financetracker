@extends('layouts.app')

@section('page', 'default')

@section('title', 'Категорії')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Категорії</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Управління категоріями доходів та витрат</p>
        </div>
        <button type="button" data-modal-target="category-modal" data-modal-toggle="category-modal" class="btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Додати категорію
        </button>
    </div>
</div>

<!-- Tabs -->
<div class="mb-6 border-b border-gray-200 dark:border-gray-700">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
        <li class="mr-2" role="presentation">
            <button class="inline-flex items-center justify-center p-4 border-b-2 border-blue-600 rounded-t-lg text-blue-600 dark:text-blue-500 dark:border-blue-500 group" id="income-tab" data-tabs-target="#income" type="button" role="tab" aria-controls="income" aria-selected="true">
                <svg class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Доходи
            </button>
        </li>
        <li class="mr-2" role="presentation">
            <button class="inline-flex items-center justify-center p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 text-gray-500 dark:text-gray-400 group" id="expense-tab" data-tabs-target="#expense" type="button" role="tab" aria-controls="expense" aria-selected="false">
                <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
                Витрати
            </button>
        </li>
        <li class="mr-2" role="presentation">
            <button class="inline-flex items-center justify-center p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 text-gray-500 dark:text-gray-400 group" id="all-tab" data-tabs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="false">
                <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                Всі
            </button>
        </li>
    </ul>
</div>

<!-- Content -->
<div id="category-content">
    <!-- Income Categories -->
    <div id="income" role="tabpanel" aria-labelledby="income-tab">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="income-categories">
            <div class="col-span-full text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Завантаження...</p>
            </div>
        </div>
    </div>

    <!-- Expense Categories -->
    <div class="hidden" id="expense" role="tabpanel" aria-labelledby="expense-tab">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="expense-categories">
            <div class="col-span-full text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Завантаження...</p>
            </div>
        </div>
    </div>

    <!-- All Categories -->
    <div class="hidden" id="all" role="tabpanel" aria-labelledby="all-tab">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="all-categories">
            <div class="col-span-full text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Завантаження...</p>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="category-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-gray-700 rounded-lg shadow">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-600">
                <h3 class="text-lg font-semibold text-white" id="modal-title">
                    Додати категорію
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-600 hover:text-white rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="category-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <form id="category-form" class="p-4 md:p-5">
                <input type="hidden" id="category-id" name="id">
                <div class="grid gap-4 mb-4">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-white">Назва</label>
                        <input type="text" name="name" id="name" class="input" placeholder="Назва категорії" required>
                    </div>
                    <div>
                        <label for="type" class="block mb-2 text-sm font-medium text-white">Тип</label>
                        <select name="type" id="type" class="input" required>
                            <option value="income">Дохід</option>
                            <option value="expense">Витрата</option>
                        </select>
                    </div>
                    <div>
                        <label for="color" class="block mb-2 text-sm font-medium text-white">Колір</label>
                        <input type="color" name="color" id="color" class="input h-12" value="#10B981" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full">
                    <span id="submit-text">Зберегти</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Глобальні змінні
let categoriesHeaders = null;
let categories = [];

document.addEventListener('DOMContentLoaded', function() {
    categoriesHeaders = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
    };
    
    // Tab switching functionality
    const tabs = document.querySelectorAll('[role="tab"]');
    const tabPanels = document.querySelectorAll('[role="tabpanel"], #income, #expense, #all');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-tabs-target');
            
            // Remove active state from all tabs
            tabs.forEach(t => {
                t.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
                t.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-600', 'hover:border-gray-300', 'dark:hover:text-gray-300');
                t.setAttribute('aria-selected', 'false');
                
                // Update icon colors
                const icon = t.querySelector('svg');
                if (icon) {
                    icon.classList.remove('text-blue-600', 'dark:text-blue-500');
                    icon.classList.add('text-gray-400', 'group-hover:text-gray-500', 'dark:text-gray-500', 'dark:group-hover:text-gray-300');
                }
            });
            
            // Add active state to clicked tab
            this.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-600', 'hover:border-gray-300', 'dark:hover:text-gray-300');
            this.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
            this.setAttribute('aria-selected', 'true');
            
            // Update icon color
            const activeIcon = this.querySelector('svg');
            if (activeIcon) {
                activeIcon.classList.remove('text-gray-400', 'group-hover:text-gray-500', 'dark:text-gray-500', 'dark:group-hover:text-gray-300');
                activeIcon.classList.add('text-blue-600', 'dark:text-blue-500');
            }
            
            // Hide all panels
            tabPanels.forEach(panel => {
                panel.classList.add('hidden');
            });
            
            // Show target panel
            const targetPanel = document.querySelector(target);
            if (targetPanel) {
                targetPanel.classList.remove('hidden');
            }
        });
    });
    
    // Load categories
    function loadCategories() {
        console.log('Loading categories...');
        fetch('/api/v1/categories', {
            headers: categoriesHeaders,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Categories loaded:', data);
            if (data.success) {
                categories = data.data.categories;
                console.log('Total categories:', categories.length);
                renderCategories();
            }
        })
        .catch(err => console.error('Error loading categories:', err));
    }
    
    // Render categories
    function renderCategories() {
        const incomeCategories = categories.filter(c => c.type === 'income');
        const expenseCategories = categories.filter(c => c.type === 'expense');
        
        document.getElementById('income-categories').innerHTML = renderCategoryCards(incomeCategories);
        document.getElementById('expense-categories').innerHTML = renderCategoryCards(expenseCategories);
        document.getElementById('all-categories').innerHTML = renderCategoryCards(categories);
    }
    
    // Render category cards
    function renderCategoryCards(cats) {
        if (cats.length === 0) {
            return '<div class="col-span-full text-center py-12"><div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg></div><p class="text-gray-500 dark:text-gray-400 text-lg">Немає категорій</p><p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Додайте нову категорію, щоб розпочати</p></div>';
        }
        
        return cats.map(cat => `
            <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full transition-all duration-200" style="background-color: ${cat.color}"></div>
                <div class="p-5 pl-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3 flex-1">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-200 group-hover:scale-110" style="background: linear-gradient(135deg, ${cat.color}20 0%, ${cat.color}10 100%)">
                                    <div class="w-6 h-6 rounded-lg shadow-sm" style="background-color: ${cat.color}"></div>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-base mb-1 truncate">${cat.name}</h3>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${cat.type === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'}">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${cat.type === 'income' ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'}"></path>
                                        </svg>
                                        ${cat.type === 'income' ? 'Дохід' : 'Витрата'}
                                    </span>
                                    ${!cat.user_id ? '<span class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>Системна</span>' : ''}
                                </div>
                            </div>
                        </div>
                        ${cat.user_id ? `
                        <div class="flex items-center space-x-1 ml-2">
                            <button onclick="editCategory(${cat.id})" class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Редагувати">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteCategory(${cat.id})" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Видалити">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // Edit category
    window.editCategory = function(id) {
        const category = categories.find(c => c.id === id);
        if (!category) return;
        
        document.getElementById('modal-title').textContent = 'Редагувати категорію';
        document.getElementById('category-id').value = category.id;
        document.getElementById('name').value = category.name;
        document.getElementById('type').value = category.type;
        document.getElementById('color').value = category.color;
        document.getElementById('submit-text').textContent = 'Оновити';
        
        // Open modal
        document.querySelector('[data-modal-target="category-modal"]').click();
    };
    
    // Delete category
    window.deleteCategory = function(id) {
        if (!confirm('Видалити цю категорію? Усі транзакції з цією категорією будуть збережені, але втратять прив\'язку до категорії.')) return;
        
        fetch(`/api/v1/categories/${id}`, {
            method: 'DELETE',
            headers: categoriesHeaders,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCategories();
            } else {
                alert(data.message || 'Помилка видалення категорії');
            }
        })
        .catch(err => {
            console.error('Error deleting category:', err);
            alert('Помилка видалення категорії');
        });
    };
    
    // Form submit
    document.getElementById('category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        const id = document.getElementById('category-id').value;
        
        console.log('Form data before submit:', data);
        console.log('Category ID:', id);
        
        const url = id ? `/api/v1/categories/${id}` : '/api/v1/categories';
        const method = id ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                ...categoriesHeaders,
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                loadCategories();
                document.querySelector('[data-modal-hide="category-modal"]').click();
                resetForm();
            } else {
                let errorMsg = data.message || 'Помилка збереження категорії';
                if (data.errors) {
                    errorMsg += '\n\nДеталі:\n' + Object.entries(data.errors)
                        .map(([field, errors]) => `${field}: ${errors.join(', ')}`)
                        .join('\n');
                }
                alert(errorMsg);
            }
        })
        .catch(err => {
            console.error('Error saving category:', err);
            alert('Помилка збереження категорії: ' + err.message);
        });
    });
    
    // Reset form when modal opens
    document.querySelector('[data-modal-target="category-modal"]').addEventListener('click', function() {
        resetForm();
    });
    
    // Reset form function
    function resetForm() {
        document.getElementById('modal-title').textContent = 'Додати категорію';
        document.getElementById('category-form').reset();
        document.getElementById('category-id').value = '';
        document.getElementById('color').value = '#10B981';
        document.getElementById('submit-text').textContent = 'Зберегти';
        
        // Встановлюємо тип відповідно до активної вкладки
        const activeTab = document.querySelector('[role="tab"][aria-selected="true"]');
        if (activeTab) {
            const tabTarget = activeTab.getAttribute('data-tabs-target');
            if (tabTarget === '#expense') {
                document.getElementById('type').value = 'expense';
            } else if (tabTarget === '#income') {
                document.getElementById('type').value = 'income';
            }
        }
    }
    
    // Load on init
    loadCategories();
});
</script>
@endpush

@endsection
