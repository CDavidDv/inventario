<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\CategoryController;

// Nuevos controladores del sistema de inventario
use App\Http\Controllers\ElementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ElementPriceController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\InventoryMovementsController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\InventoryExcelController;
use Illuminate\Support\Facades\Route;

// NO AUTH ROUTES 
Route::get('/', [DashboardController::class, 'index']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // AUTH ROUTES
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name("dashboard");
        
    // Rutas de Inventario
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventoryDashboardController::class, 'index'])->name('index');
        Route::get('/stock-chart', [InventoryDashboardController::class, 'getStockChart'])->name('stock-chart');
        Route::post('/alerts/{alert}/read', [InventoryDashboardController::class, 'markAlertAsRead'])->name('alerts.read');
    });
    
    // Rutas de Items
    Route::resource('items', ItemController::class);
    Route::post('/items/{item}/adjust-stock', [ItemController::class, 'adjustStock'])->name('items.adjust-stock');
    Route::get('/items/type/{type}', [ItemController::class, 'getByType'])->name('items.by-type');
    Route::get('/items/category/{categoryId}', [ItemController::class, 'getByCategory'])->name('items.by-category');
    Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
    
    // Rutas de Asignaciones Jerárquicas
    Route::post('/items/{kit}/assign', [ItemController::class, 'assignComponent'])->name('items.assign');
    Route::delete('/items/{kit}/unassign/{component}', [ItemController::class, 'unassignComponent'])->name('items.unassign');
    Route::get('/items/{kit}/components', [ItemController::class, 'getComponents'])->name('items.components');
    Route::get('/items/available-elements', [ItemController::class, 'getAvailableElements'])->name('items.available-elements');
    
    // Rutas de Estado Activo/Inactivo
    Route::post('/items/{item}/toggle-active', [ItemController::class, 'toggleActive'])->name('items.toggle-active');

    // Rutas de Exportación e Importación de Excel
    Route::prefix('inventory-excel')->name('inventory-excel.')->group(function () {
        Route::get('/export', [InventoryExcelController::class, 'export'])->name('export');
        Route::post('/import', [InventoryExcelController::class, 'import'])->name('import');
        Route::post('/preview', [InventoryExcelController::class, 'preview'])->name('preview');
        Route::get('/template', [InventoryExcelController::class, 'downloadTemplate'])->name('template');
    });

    // Rutas de Categorías
    Route::resource('categories', CategoryController::class);
    Route::post('/categories/{category}/deactivate', [CategoryController::class, 'deactivate'])->name('categories.deactivate');
    Route::post('/categories/{category}/reactivate', [CategoryController::class, 'reactivate'])->name('categories.reactivate');
    Route::get('/categories/type/{type}', [CategoryController::class, 'getByType'])->name('categories.by-type');

    Route::get('/profile',  [ProfileController::class, 'show'])->name('profile.show');
    
    // Rutas de Administración
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Gestión de Itemes
        Route::get('/items', [AdminController::class, 'items'])->name('items');
        Route::get('/items/create', [AdminController::class, 'createItem'])->name('items.create');
        Route::post('/items', [AdminController::class, 'storeItem'])->name('items.store');
        Route::get('/items/{item}/edit', [AdminController::class, 'editItem'])->name('items.edit');
        Route::put('/items/{item}', [AdminController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{item}', [AdminController::class, 'deleteItem'])->name('items.delete');
        
        // Gestión de Kits
        Route::get('/kits', [AdminController::class, 'kits'])->name('kits');
        Route::get('/kits/create', [AdminController::class, 'createKit'])->name('kits.create');
        Route::post('/kits', [AdminController::class, 'storeKit'])->name('kits.store');
        
        // Gestión de Usuarios
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        
        // Log de Movimientos
        Route::get('/movements', [AdminController::class, 'movements'])->name('movements');
        Route::get('/movements/export', [AdminController::class, 'exportMovements'])->name('movements.export');
    });
    
    // Rutas de Trabajadores
    Route::prefix('worker')->name('worker.')->group(function () {
        Route::get('/dashboard', [WorkerController::class, 'dashboard'])->name('dashboard');
        Route::get('/inventory', [WorkerController::class, 'inventory'])->name('inventory');
        Route::post('/add-stock', [WorkerController::class, 'addStock'])->name('add-stock');
        Route::post('/remove-stock', [WorkerController::class, 'removeStock'])->name('remove-stock');
        Route::post('/adjust-stock', [WorkerController::class, 'adjustStock'])->name('adjust-stock');
        Route::get('/movements', [WorkerController::class, 'movements'])->name('movements');
        Route::get('/concepts', [WorkerController::class, 'getConcepts'])->name('concepts');
        
        // Búsqueda y detalles de itemes
        Route::get('/search-items', [WorkerController::class, 'searchItems'])->name('search-items');
        Route::get('/items/{id}/details', [WorkerController::class, 'getItemDetails'])->name('item-details');
    });
    
    // Rutas de Supervisores
    Route::prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/', [SupervisorController::class, 'dashboard'])->name('dashboard');
        Route::get('/inventory', [SupervisorController::class, 'inventory'])->name('inventory');
        Route::put('/items/{item}/limits', [SupervisorController::class, 'updateStockLimits'])->name('items.limits');
        Route::post('/items/bulk-limits', [SupervisorController::class, 'bulkUpdateLimits'])->name('items.bulk-limits');
        Route::get('/stock-analysis', [SupervisorController::class, 'stockAnalysis'])->name('stock-analysis');
        Route::get('/category-analysis', [SupervisorController::class, 'categoryAnalysis'])->name('category-analysis');
        Route::get('/stock-report/export', [SupervisorController::class, 'exportStockReport'])->name('stock-report.export');

        // Rutas de administración de usuarios
        Route::get('/users/{user}', [SupervisorController::class, 'show'])->name('users.show');
        Route::post('/users', [SupervisorController::class, 'createUser'])->name('users.create');
        Route::post('/users/{user}/toggle-active', [SupervisorController::class, 'toggleUserActive'])->name('users.toggle-active');
        Route::put('/users/{user}', [SupervisorController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/roles', [SupervisorController::class, 'updateUserRoles'])->name('users.roles');
        Route::delete('/users/{user}', [SupervisorController::class, 'deleteUser'])->name('users.delete');
    });

    // Rutas de Administración de Permisos (Solo Admins)
    Route::prefix('admin/permissions')->name('permissions.')->middleware(['permission:manage-permissions'])->group(function () {
        Route::get('/', [PermissionsController::class, 'index'])->name('index');
        Route::post('/roles/{role}', [PermissionsController::class, 'updateRolePermissions'])->name('roles.update');
    });

    // API para obtener permisos del usuario
    Route::get('/api/user-permissions', [PermissionsController::class, 'getUserPermissions'])->name('api.user-permissions');

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        Route::post('/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::prefix('/production')->name('production.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProductionController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\ProductionController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ProductionController::class, 'store'])->name('store');
        Route::get('/{production}', [\App\Http\Controllers\ProductionController::class, 'show'])->name('show');
        Route::get('/{production}/edit', [\App\Http\Controllers\ProductionController::class, 'edit'])->name('edit');
        Route::put('/{production}', [\App\Http\Controllers\ProductionController::class, 'update'])->name('update');
        Route::delete('/{production}', [\App\Http\Controllers\ProductionController::class, 'destroy'])->name('destroy');
        Route::post('/{production}/start', [\App\Http\Controllers\ProductionController::class, 'start'])->name('start');
        Route::post('/{production}/complete', [\App\Http\Controllers\ProductionController::class, 'complete'])->name('complete');
        Route::post('/{production}/cancel', [\App\Http\Controllers\ProductionController::class, 'cancel'])->name('cancel');
        Route::post('/{production}/update-progress', [\App\Http\Controllers\ProductionController::class, 'updateProgress'])->name('update-progress');
        
        // Nuevas rutas para agregar elementos, componentes y kits
        Route::post('/add-element', [\App\Http\Controllers\ProductionController::class, 'addElement'])->name('add-element');
        Route::post('/add-component', [\App\Http\Controllers\ProductionController::class, 'addComponent'])->name('add-component');
        Route::post('/add-kit', [\App\Http\Controllers\ProductionController::class, 'addKit'])->name('add-kit');
        
        // Rutas para restar del inventario
        Route::post('/mark-defective', [\App\Http\Controllers\ProductionController::class, 'markAsDefective'])->name('mark-defective');
        Route::post('/mark-damaged', [\App\Http\Controllers\ProductionController::class, 'markAsDamaged'])->name('mark-damaged');
        Route::post('/register-return', [\App\Http\Controllers\ProductionController::class, 'registerReturn'])->name('register-return');
        Route::post('/register-internet-sale', [\App\Http\Controllers\ProductionController::class, 'registerInternetSale'])->name('register-internet-sale');
        Route::post('/register-sale', [\App\Http\Controllers\ProductionController::class, 'registerSale'])->name('register-sale');
        Route::post('/register-mercadolibre-sale', [\App\Http\Controllers\ProductionController::class, 'registerMercadoLibreSale'])->name('register-mercadolibre-sale');
        Route::post('/register-website-sale', [\App\Http\Controllers\ProductionController::class, 'registerWebsiteSale'])->name('register-website-sale');
        
        // Rutas API para obtener datos
        Route::get('/api/items/{type}', [\App\Http\Controllers\ProductionController::class, 'getAvailableItems'])->name('api.items');
        Route::get('/api/suppliers', [\App\Http\Controllers\ProductionController::class, 'getActiveSuppliers'])->name('api.suppliers');
        Route::get('/api/components/{itemId}', [\App\Http\Controllers\ProductionController::class, 'getItemComponents'])->name('api.components');
    });

    // Rutas de Elementos
    
    Route::get('/elements', [ElementController::class, 'index'])->name('elements.index');
    Route::post('/elements/{element}/update-stock', [ElementController::class, 'updateStock'])
        ->name('elements.update-stock');
    
    // Rutas de Proveedores
    Route::resource('suppliers', SupplierController::class);
    
    // Rutas de Precios de Elementos
    Route::resource('element-prices', ElementPriceController::class)
        ->except(['show'])
        ->names([
            'index' => 'element-prices.index',
            'create' => 'element-prices.create',
            'store' => 'element-prices.store',
            'edit' => 'element-prices.edit',
            'update' => 'element-prices.update',
            'destroy' => 'element-prices.destroy'
        ]);
    
    // Rutas de Kits (nuevo sistema)
    Route::resource('inventory-kits', KitController::class)
        ->names([
            'index' => 'inventory-kits.index',
            'create' => 'inventory-kits.create',
            'store' => 'inventory-kits.store',
            'show' => 'inventory-kits.show',
            'edit' => 'inventory-kits.edit',
            'update' => 'inventory-kits.update',
            'destroy' => 'inventory-kits.destroy'
        ]);
    Route::post('/inventory-kits/{kit}/assemble', [KitController::class, 'assemble'])
        ->name('inventory-kits.assemble');
    
    // Rutas de Alertas de Stock
    Route::resource('stock-alerts', StockAlertController::class)->only(['index', 'update', 'destroy']);
    Route::post('/stock-alerts/{stockAlert}/mark-read', [StockAlertController::class, 'markAsRead'])
        ->name('stock-alerts.mark-read');
    Route::post('/stock-alerts/{stockAlert}/resolve', [StockAlertController::class, 'resolve'])
        ->name('stock-alerts.resolve');
    Route::post('/stock-alerts/bulk-mark-read', [StockAlertController::class, 'bulkMarkAsRead'])
        ->name('stock-alerts.bulk-mark-read');
    
    // Rutas de Reportes del Sistema de Inventario
    Route::prefix('inventory-reports')->name('inventory-reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/stock-status', [ReportsController::class, 'stockStatus'])->name('stock-status');
        Route::get('/stock-movements', [ReportsController::class, 'inventoryMovements'])->name('stock-movements');
        Route::get('/stock-alerts', [ReportsController::class, 'stockAlerts'])->name('stock-alerts');
        Route::get('/abc-analysis', [ReportsController::class, 'abcAnalysis'])->name('abc-analysis');
        Route::get('/elements-export', [ReportsController::class, 'exportElements'])->name('elements-export');
        Route::get('/low-stock-export', [ReportsController::class, 'exportLowStock'])->name('low-stock-export');
        Route::get('/low-stock-alerts', [ReportsController::class, 'exportLowStock'])->name('low-stock-alerts');
    });
    
    // Rutas de Analytics Dashboard
    Route::get('/analytics', [ReportsController::class, 'analyticsDashboard'])
        ->name('analytics.dashboard');
    
    // Rutas de Movimientos de Inventario
    Route::prefix('inventory-movements')->name('inventory-movements.')->group(function () {
        Route::get('/', [InventoryMovementsController::class, 'index'])->name('index');
        Route::get('/excel-imports', [InventoryMovementsController::class, 'excelImports'])->name('excel-imports');
        Route::get('/{movement}', [InventoryMovementsController::class, 'show'])->name('show');
        Route::get('/export/csv', [InventoryMovementsController::class, 'export'])->name('export');
        Route::get('/api/dashboard', [InventoryMovementsController::class, 'dashboard'])->name('dashboard');
    });
    
    // Rutas de Sistema de Auditoría (Solo Administradores)
    Route::prefix('system-logs')->name('system-logs.')->group(function () {
        Route::get('/', [SystemLogController::class, 'index'])->name('index');
        Route::get('/{systemLog}', [SystemLogController::class, 'show'])->name('show');
        Route::get('/export/csv', [SystemLogController::class, 'export'])->name('export');
        Route::get('/api/dashboard', [SystemLogController::class, 'dashboard'])->name('dashboard');
        Route::post('/cleanup', [SystemLogController::class, 'cleanup'])->name('cleanup');
    });
    
    // API Routes para el sistema de inventario (para uso con AJAX/Vue)
    Route::prefix('api/inventory')->name('api.inventory.')->group(function () {
        Route::get('/elements/search', [ElementController::class, 'search'])->name('elements.search');
        Route::get('/elements/{element}/stock-history', [ElementController::class, 'stockHistory'])->name('elements.stock-history');
        Route::get('/kits/{kit}/availability', [KitController::class, 'checkAvailability'])->name('kits.availability');
        Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
        Route::get('/dashboard-stats', [ElementController::class, 'dashboardStats'])->name('dashboard-stats');

        // Búsqueda por QR
        Route::get('/search-by-qr', [ItemController::class, 'searchByQr'])->name('search-by-qr');

        // API Routes para Analytics y Charts
        Route::get('/dashboard-kpis', [ReportsController::class, 'getDashboardKpis'])->name('dashboard-kpis');
        Route::get('/stock-movements-chart', [ReportsController::class, 'getStockMovementsChart'])->name('stock-movements-chart');
        Route::get('/category-stats', [ReportsController::class, 'getCategoryStats'])->name('category-stats');
        Route::get('/alerts-trend', [ReportsController::class, 'getAlertsTrend'])->name('alerts-trend');
        Route::get('/abc-summary', [ReportsController::class, 'getAbcSummary'])->name('abc-summary');
        Route::get('/top-value-items', [ReportsController::class, 'getTopValueItems'])->name('top-value-items');
        Route::get('/top-turnover-items', [ReportsController::class, 'getTopTurnoverItems'])->name('top-turnover-items');
    });

    // API Routes para notificaciones
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/notifications', [StockAlertController::class, 'getNotifications'])->name('notifications');
        Route::post('/notifications/{alert}/mark-read', [StockAlertController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [StockAlertController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/{alert}/resolve', [StockAlertController::class, 'resolve'])->name('notifications.resolve');
    });
    
});

//si no se encuentra el link regresar a la pagina principal
Route::fallback(function () {
    return redirect('/');
});