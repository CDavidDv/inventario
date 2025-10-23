<template>
    <AppLayout title="Inventario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Inventario
            </h2>
        </template>
        
        <Container class="shadow-2xl ">
            <div class="py-4 space-y-8 mb-8">
                <!-- RESUMEN DE ESTADOS DEL STOCK -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div class="relative group border-l-4 border-red-500 rounded-lg p-4 shadow-lg cursor-help">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                            <div>
                                <div class="text-2xl font-bold text-red-600">{{ getStockStatusCount('bajo_stock') }}
                                </div>
                                <div class="text-sm text-red-700">Bajo Stock</div>
                            </div>
                        </div>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                            Items con stock menor al mínimo establecido
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>

                    <div class="relative group border-l-4 border-yellow-500 rounded-lg p-4 shadow-lg cursor-help">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                            <div>
                                <div class="text-2xl font-bold text-yellow-600">{{ getStockStatusCount('en_minimo')
                                    }}</div>
                                <div class="text-sm text-yellow-700">En el Mínimo</div>
                            </div>
                        </div>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                            Items con stock exactamente igual al mínimo
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>

                    <div class="relative group border-l-4 border-green-500 rounded-lg p-4 shadow-lg cursor-help">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <div>
                                <div class="text-2xl font-bold text-green-600">{{ getStockStatusCount('normal') }}
                                </div>
                                <div class="text-sm text-green-700">Stock Normal</div>
                            </div>
                        </div>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                            Items con stock entre mínimo y máximo
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>

                    <div class="relative group border-l-4 border-blue-500 rounded-lg p-4 shadow-lg cursor-help">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                            <div>
                                <div class="text-2xl font-bold text-blue-600">{{ getStockStatusCount('sobre_stock')
                                    }}</div>
                                <div class="text-sm text-blue-700">Sobre Stock</div>
                            </div>
                        </div>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                            Items con stock mayor al máximo establecido
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE ELEMENTOS -->
                <div class="border border-gray-200 shadow-xl rounded-lg py-3 md:py-4 px-3 md:px-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-3">
                        <div class="min-w-0">
                            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Elementos Individuales</h2>
                            <p class="text-sm md:text-base text-gray-600">Gestiona los elementos individuales de tu inventario</p>
                        </div>
                        <div class="grid grid-cols-2 sm:flex sm:flex-row gap-2">
                            <!-- Botón Exportar -->
                            <div class="relative group">
                                <button @click="exportItems('element')"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-1 sm:gap-2 transition-colors w-full text-xs sm:text-sm">
                                    <Download class="w-4 h-4 flex-shrink-0" />
                                    <span>Exportar</span>
                                </button>
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Exportar elementos a Excel
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Importar -->
                            <div class="relative group">
                                <button @click="openImportModal('element')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-1 sm:gap-2 transition-colors w-full text-xs sm:text-sm">
                                    <Upload class="w-4 h-4 flex-shrink-0" />
                                    <span>Importar</span>
                                </button>
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Importar elementos desde Excel
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Gestionar Categorías -->
                            <div class="relative group">
                                <button @click="openCategoryModal('element', 'add')"
                                    class="bg-green-600 hover:bg-green-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-1 sm:gap-2 transition-colors w-full text-xs sm:text-sm">
                                    <FolderPlus class="w-4 h-4 flex-shrink-0" />
                                    <span class="hidden sm:inline">Gestionar Categorías</span>
                                    <span class="sm:hidden">Categorías</span>
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Crear, editar o eliminar categorías para elementos
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Nuevo Elemento -->
                            <div class="relative group">
                                <button @click="handleNewItemClick('element')"
                                    :disabled="!canCreateItem('element')"
                                    :class="[
                                        'px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-1 sm:gap-2 transition-colors w-full text-xs sm:text-sm',
                                        canCreateItem('element')
                                            ? 'bg-blue-600 hover:bg-blue-700 text-white'
                                            : 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                    ]">
                                    <Plus class="w-4 h-4 flex-shrink-0" />
                                    <span class="hidden sm:inline">Nuevo Elemento</span>
                                    <span class="sm:hidden">Nuevo</span>
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    {{ getButtonTooltip('element') }}
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros para Elementos -->
                    <div class="bg-white rounded-lg shadow-sm p-3 md:p-4 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
                            <h3 class="text-sm font-medium text-gray-700">Filtros</h3>
                            <div v-if="getActiveElementFiltersCount > 0" class="flex items-center gap-2">
                                <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                                    {{ getActiveElementFiltersCount }} filtro(s) activo(s)
                                </span>
                            </div>
                        </div>
                        
                        <!-- Layout responsive de filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                            <!-- Búsqueda - ocupa 2 columnas en desktop -->
                            <div class="relative group lg:col-span-2">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                                <input v-model="elementFilters.search" 
                                        @input="resetPageOnFilterChange('element', 'search')" 
                                        type="text" 
                                        placeholder="Buscar elementos..."
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full text-sm" />
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Buscar por nombre, descripción o categoría
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            
                            <!-- Categorías -->
                            <div class="relative">
                                <select v-model="elementFilters.category" 
                                        @change="resetPageOnFilterChange('element', 'category')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todas las categorías</option>
                                    <option v-for="category in elementCategories" :key="category.id" :value="category.name">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Estado de Stock -->
                            <div class="relative group">
                                <select v-model="elementFilters.stock" 
                                        @change="resetPageOnFilterChange('element', 'stock')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todos los stocks</option>
                                    <option value="bajo_stock">Bajo stock</option>
                                    <option value="en_minimo">En el mínimo</option>
                                    <option value="normal">Stock normal</option>
                                    <option value="sobre_stock">Sobre stock</option>
                                </select>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Filtrar por estado de stock
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            
                            <!-- Estado Activo/Inactivo -->
                            <div class="relative">
                                <select v-model="elementFilters.status" 
                                        @change="resetPageOnFilterChange('element', 'status')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>
                            
                            <!-- Botón Limpiar -->
                            <div class="relative group">
                                <button @click="clearElementFilters"
                                        class="w-full px-3 py-2 text-sm text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                                    Limpiar
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Limpiar todos los filtros
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Elementos -->
                    <InventoryTable :items="paginatedElementItems" :is-loading="isLoadingElements"
                        :total-items="elementItems.length" :filtered-count="filteredElementItems.length"
                        :sort-field="elementSort.field" :sort-direction="elementSort.direction" @sort="sortElements"
                        @edit="openModal('element', 'edit', $event)" @delete="confirmDelete('element', $event)"
                        @assign="openModal('element', 'assign', $event)"
                        @toggle-active="toggleItemActive('element', $event)" @view="viewItem" 
                        typeItem="element"
                        />

                    <!-- Paginación de Elementos -->
                    <div v-if="elementPagination.totalPages > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ elementPagination.start }} a {{ elementPagination.end }} de {{
                            elementPagination.total }}
                            resultados
                        </div>
                        <div class="flex space-x-2">
                            <button @click="goToElementPage(elementPagination.currentPage - 1)"
                                :disabled="elementPagination.currentPage === 1"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                Anterior
                            </button>
                            <button v-for="page in Math.min(5, elementPagination.totalPages)" :key="page"
                                @click="goToElementPage(page)"
                                :class="page === elementPagination.currentPage ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1 text-sm border rounded">
                                {{ page }}
                            </button>
                            <button @click="goToElementPage(elementPagination.currentPage + 1)"
                                :disabled="elementPagination.currentPage === elementPagination.totalPages"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE COMPONENTES -->
                <div class="border border-gray-200 shadow-xl rounded-lg py-3 md:py-4 px-3 md:px-6 mt-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-3">
                        <div class="min-w-0">
                            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Componentes</h2>
                            <p class="text-sm md:text-base text-gray-600">Gestiona los componentes que tienen varios items asociados</p>
                        </div>
                        <div class="grid grid-cols-2 sm:flex sm:flex-row gap-2">
                            <!-- Botón Exportar -->
                            <div class="relative group">
                                <button @click="exportItems('component')"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm">
                                    <Download class="w-4 h-4 flex-shrink-0" />
                                    <span>Exportar</span>
                                </button>
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Exportar componentes a Excel
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Importar -->
                            <div class="relative group">
                                <button @click="openImportModal('component')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm">
                                    <Upload class="w-4 h-4 flex-shrink-0" />
                                    <span>Importar</span>
                                </button>
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Importar componentes desde Excel
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Gestionar Categorías -->
                            <div class="relative group">
                                <button @click="openCategoryModal('component', 'add')"
                                    class="bg-green-600 hover:bg-green-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm">
                                    <FolderPlus class="w-4 h-4 flex-shrink-0" />
                                    <span class="hidden sm:inline">Gestionar Categorías</span>
                                    <span class="sm:hidden">Categorías</span>
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Crear, editar o eliminar categorías para componentes
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Nuevo Componente -->
                            <div class="relative group">
                                <button @click="handleNewItemClick('component')"
                                    :disabled="!canCreateItem('component')"
                                    :class="[
                                        'px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm',
                                        canCreateItem('component')
                                            ? 'bg-blue-600 hover:bg-blue-700 text-white'
                                            : 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                    ]">
                                    <Plus class="w-4 h-4 flex-shrink-0" />
                                    <span class="hidden sm:inline">Nuevo Componente</span>
                                    <span class="sm:hidden">Nuevo</span>
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    {{ getButtonTooltip('component') }}
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros para Componentes -->
                    <div class="bg-white rounded-lg shadow-sm p-3 md:p-4 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
                            <h3 class="text-sm font-medium text-gray-700">Filtros</h3>
                            <div v-if="getActiveComponentFiltersCount > 0" class="flex items-center gap-2">
                                <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                                    {{ getActiveComponentFiltersCount }} filtro(s) activo(s)
                                </span>
                            </div>
                        </div>
                        
                        <!-- Layout responsive de filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                            <!-- Búsqueda - ocupa 2 columnas en desktop -->
                            <div class="relative group lg:col-span-2">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                                <input v-model="componentFilters.search" 
                                        @input="resetPageOnFilterChange('component', 'search')" 
                                        type="text"
                                        placeholder="Buscar componentes..."
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full text-sm" />
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Buscar por nombre, descripción o categoría
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            
                            <!-- Categorías -->
                            <div class="relative">
                                <select v-model="componentFilters.category" 
                                        @change="resetPageOnFilterChange('component', 'category')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todas las categorías</option>
                                    <option v-for="category in componentCategories" :key="category.id" :value="category.name">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Estado de Stock -->
                            <div class="relative group">
                                <select v-model="componentFilters.stock" 
                                        @change="resetPageOnFilterChange('component', 'stock')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todos los stocks</option>
                                    <option value="bajo_stock">Bajo stock</option>
                                    <option value="en_minimo">En el mínimo</option>
                                    <option value="normal">Stock normal</option>
                                    <option value="sobre_stock">Sobre stock</option>
                                </select>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Filtrar por estado de stock
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            
                            <!-- Estado Activo/Inactivo -->
                            <div class="relative">
                                <select v-model="componentFilters.status" 
                                        @change="resetPageOnFilterChange('component', 'status')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Botón Limpiar - separado en móviles -->
                        <div class="mt-3 lg:mt-0">
                            <div class="relative group lg:inline-block">
                                <button @click="clearComponentFilters"
                                        class="w-full lg:w-auto px-3 py-2 text-sm text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                                    Limpiar Filtros
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Limpiar todos los filtros
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Componentes -->
                    <InventoryTable :items="paginatedComponentItems" :is-loading="isLoadingComponents"
                        :total-items="componentItems.length" :filtered-count="filteredComponentItems.length"
                        :sort-field="componentSort.field" :sort-direction="componentSort.direction"
                        @sort="sortComponents" @edit="openModal('component', 'edit', $event)"
                        @delete="confirmDelete('component', $event)"
                        @assign="openModal('component', 'assign', $event)"
                        @toggle-active="toggleItemActive('component', $event)" @view="viewItem" 
                        typeItem="component"
                        />

                    <!-- Paginación de Componentes -->
                    <div v-if="componentPagination.totalPages > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ componentPagination.start }} a {{ componentPagination.end }} de {{
                            componentPagination.total }} resultados
                        </div>
                        <div class="flex space-x-2">
                            <button @click="goToComponentPage(componentPagination.currentPage - 1)"
                                :disabled="componentPagination.currentPage === 1"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                Anterior
                            </button>
                            <button v-for="page in Math.min(5, componentPagination.totalPages)" :key="page"
                                @click="goToComponentPage(page)"
                                :class="page === componentPagination.currentPage ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1 text-sm border rounded">
                                {{ page }}
                            </button>
                            <button @click="goToComponentPage(componentPagination.currentPage + 1)"
                                :disabled="componentPagination.currentPage === componentPagination.totalPages"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE KITS -->
                <div class="border border-gray-200 shadow-xl rounded-lg py-3 md:py-4 px-3 md:px-6 mt-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-3">
                        <div class="min-w-0">
                            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Kits</h2>
                            <p class="text-sm md:text-base text-gray-600">Gestiona los kits que contienen múltiples elementos</p>
                        </div>
                        <div class="grid grid-cols-2 sm:flex sm:flex-row gap-2">
                            <!-- Botón Exportar -->
                            <div class="relative group">
                                <button @click="exportItems('kit')"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm">
                                    <Download class="w-4 h-4 flex-shrink-0" />
                                    <span>Exportar</span>
                                </button>
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Exportar kits a Excel
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Importar -->
                            <div class="relative group">
                                <button @click="openImportModal('kit')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm">
                                    <Upload class="w-4 h-4 flex-shrink-0" />
                                    <span>Importar</span>
                                </button>
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Importar kits desde Excel
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Gestionar Categorías -->
                            <div class="relative group">
                                <button @click="openCategoryModal('kit', 'add')"
                                    class="bg-green-600 hover:bg-green-700 text-white px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm">
                                    <FolderPlus class="w-4 h-4 flex-shrink-0" />
                                    <span class="hidden sm:inline">Gestionar Categorías</span>
                                    <span class="sm:hidden">Categorías</span>
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Crear, editar o eliminar categorías para kits
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>

                            <!-- Botón Nuevo Kit -->
                            <div class="relative group">
                                <button @click="handleNewItemClick('kit')"
                                    :disabled="!canCreateItem('kit')"
                                    :class="[
                                        'px-2 sm:px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors w-full sm:w-auto text-xs sm:text-sm',
                                        canCreateItem('kit')
                                            ? 'bg-blue-600 hover:bg-blue-700 text-white'
                                            : 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                    ]">
                                    <Plus class="w-4 h-4 flex-shrink-0" />
                                    <span class="hidden sm:inline">Nuevo Kit</span>
                                    <span class="sm:hidden">Nuevo</span>
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    {{ getButtonTooltip('kit') }}
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros para Kits -->
                    <div class="bg-white rounded-lg shadow-sm p-3 md:p-4 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
                            <h3 class="text-sm font-medium text-gray-700">Filtros</h3>
                            <div v-if="getActiveKitFiltersCount > 0" class="flex items-center gap-2">
                                <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                                    {{ getActiveKitFiltersCount }} filtro(s) activo(s)
                                </span>
                            </div>
                        </div>
                        
                        <!-- Layout responsive de filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                            <!-- Búsqueda - ocupa 2 columnas en desktop -->
                            <div class="relative group lg:col-span-2">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                                <input v-model="kitFilters.search" 
                                        @input="resetPageOnFilterChange('kit', 'search')" 
                                        type="text" 
                                        placeholder="Buscar kits..."
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full text-sm" />
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Buscar por nombre, descripción o categoría
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            
                            <!-- Categorías -->
                            <div class="relative">
                                <select v-model="kitFilters.category" 
                                        @change="resetPageOnFilterChange('kit', 'category')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todas las categorías</option>
                                    <option v-for="category in kitCategories" :key="category.id" :value="category.name">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Estado de Stock -->
                            <div class="relative group">
                                <select v-model="kitFilters.stock" 
                                        @change="resetPageOnFilterChange('kit', 'stock')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todos los stocks</option>
                                    <option value="bajo_stock">Bajo stock</option>
                                    <option value="en_minimo">En el mínimo</option>
                                    <option value="normal">Stock normal</option>
                                    <option value="sobre_stock">Sobre stock</option>
                                </select>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Filtrar por estado de stock
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            
                            <!-- Estado Activo/Inactivo -->
                            <div class="relative">
                                <select v-model="kitFilters.status" 
                                        @change="resetPageOnFilterChange('kit', 'status')"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Botón Limpiar - separado en móviles -->
                        <div class="mt-3 lg:mt-0">
                            <div class="relative group lg:inline-block">
                                <button @click="clearKitFilters"
                                        class="w-full lg:w-auto px-3 py-2 text-sm text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                                    Limpiar Filtros
                                </button>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    Limpiar todos los filtros
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Kits -->
                    <InventoryTable :items="paginatedKitItems" :is-loading="isLoadingKits"
                        :total-items="kitItems.length" :filtered-count="filteredKitItems.length"
                        :sort-field="kitSort.field" :sort-direction="kitSort.direction" @sort="sortKits"
                        @edit="openModal('kit', 'edit', $event)" @delete="confirmDelete('kit', $event)"
                        @assign="openModal('kit', 'assign', $event)"
                        @toggle-active="toggleItemActive('kit', $event)" @view="viewItem" 
                        typeItem="kit"
                        />

                    <!-- Paginación de Kits -->
                    <div v-if="kitPagination.totalPages > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ kitPagination.start }} a {{ kitPagination.end }} de {{ kitPagination.total
                            }} resultados
                        </div>
                        <div class="flex space-x-2">
                            <button @click="goToKitPage(kitPagination.currentPage - 1)"
                                :disabled="kitPagination.currentPage === 1"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                Anterior
                            </button>
                            <button v-for="page in Math.min(5, kitPagination.totalPages)" :key="page"
                                @click="goToKitPage(page)"
                                :class="page === kitPagination.currentPage ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1 text-sm border rounded">
                                {{ page }}
                            </button>
                            <button @click="goToKitPage(kitPagination.currentPage + 1)"
                                :disabled="kitPagination.currentPage === kitPagination.totalPages"
                                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>


                
            </div>
        </Container>

        <!-- MODAL PRINCIPAL PARA ITEMS -->
        <ItemModal v-if="showItemModal" :show="showItemModal" :type="currentItemType" :item="selectedItem"
            :categories="getCategoriesByType(currentItemType)" :modal-type="itemModalAction" @close="closeItemModal"
            @submit="handleItemSubmit" :elements="elementItems" :kits="kitItems" :components="componentItems" />

        <!-- MODAL PARA CATEGORÍAS -->
        <CategoryModal v-if="showCategoryModal" :show="showCategoryModal" :type="currentCategoryType"
            :category="selectedCategory" :categories="getCategoriesByType(currentCategoryType)"
            @close="closeCategoryModal" @submit="handleCategorySubmit" />

        <!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
        <DeleteConfirmationModal v-if="showDeleteModal" :show="showDeleteModal" :item="itemToDelete"
            :type="deleteModalType" @close="showDeleteModal = false" @confirm="deleteItem" />

        <!-- MODAL DE ASIGNACIÓN -->
        <AssignmentModal :show="showAssignmentModal" :item="currentItem" :item-type="assignmentType"
            :available-items="availableItemsForAssignment" :assigned-items="assignedItems"
            @close="closeAssignmentModal" @remove-assignment="removeAssignment" />

        <!-- MODAL DE IMPORTACIÓN DE EXCEL -->
        <ExcelImportModal
            :show="showImportModal"
            :type="importType"
            @close="closeImportModal"
            @imported="handleImportSuccess" />

    </AppLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import InventoryTable from '@/Components/InventoryTable.vue'
import ItemModal from '@/Components/ItemModal.vue'
import CategoryModal from '@/Components/CategoryModal.vue'
import DeleteConfirmationModal from '@/Components/DeleteConfirmationModal.vue'
import AssignmentModal from '@/Components/AssignmentModal.vue'
import ExcelImportModal from '@/Components/ExcelImportModal.vue'
import axios from '@/axios-config'
import Swal from 'sweetalert2'
import { FolderPlus, Plus, Search, Download, Upload } from 'lucide-vue-next'
import { type } from 'jquery';


// Props del backend
const props = defineProps({
    stats: Object,
    elementCategories: Array,
    kitCategories: Array,
    componentCategories: Array,
    elementItems: Array,
    kitItems: Array,
    componentItems: Array,
    elements: Array,
    kits: Array,
    components: Array
})

//console.log('elements', props.elements)

// Estado reactivo
const isLoadingElements = ref(false)
const isLoadingKits = ref(false)
const isLoadingComponents = ref(false)

// Filtros por tipo
const elementFilters = reactive({
    search: '',
    category: '',
    stock: '',
    status: '',
    page: 1,
    perPage: 10
})

const kitFilters = reactive({
    search: '',
    category: '',
    stock: '',
    status: '',
    page: 1,
    perPage: 10
})

const componentFilters = reactive({
    search: '',
    category: '',
    stock: '',
    status: '',
    page: 1,
    perPage: 10
})

// Ordenamiento por tipo
const elementSort = reactive({ field: '', direction: 'asc' })
const kitSort = reactive({ field: '', direction: 'asc' })
const componentSort = reactive({ field: '', direction: 'asc' })

// Modales
const showItemModal = ref(false)
const showCategoryModal = ref(false)
const showDeleteModal = ref(false)
const showAssignmentModal = ref(false)
const showImportModal = ref(false)

const itemModalType = ref('element') // element, kit, component
const itemModalAction = ref('add') // add, edit, assign
const currentItemType = ref('element')
const selectedItem = ref(null)
const importType = ref('element') // element, component, kit

// Variables del modal de asignación
const currentItem = ref(null)
const assignmentType = ref('')
const assignedItems = ref([])

const categoryModalType = ref('element') // element, kit, component
const currentCategoryType = ref('element')
const selectedCategory = ref(null)

const deleteModalType = ref('element')
const itemToDelete = ref(null)

// Computed properties para filtros
const filteredElementItems = computed(() => {
    return filterItems(props.elementItems, elementFilters, elementSort)
})

const filteredKitItems = computed(() => {
    return filterItems(props.kitItems, kitFilters, kitSort)
})

const filteredComponentItems = computed(() => {
    return filterItems(props.componentItems, componentFilters, componentSort)
})

// Computed para items paginados
const paginatedElementItems = computed(() => {
    const start = (elementFilters.page - 1) * elementFilters.perPage
    const end = start + elementFilters.perPage
    return filteredElementItems.value.slice(start, end)
})

const paginatedKitItems = computed(() => {
    const start = (kitFilters.page - 1) * kitFilters.perPage
    const end = start + kitFilters.perPage
    return filteredKitItems.value.slice(start, end)
})

const paginatedComponentItems = computed(() => {
    const start = (componentFilters.page - 1) * componentFilters.perPage
    const end = start + componentFilters.perPage
    return filteredComponentItems.value.slice(start, end)
})

// Computed para información de paginación
const elementPagination = computed(() => {
    const total = filteredElementItems.value.length
    const totalPages = Math.ceil(total / elementFilters.perPage)
    return {
        total,
        totalPages,
        currentPage: elementFilters.page,
        perPage: elementFilters.perPage,
        start: (elementFilters.page - 1) * elementFilters.perPage + 1,
        end: Math.min(elementFilters.page * elementFilters.perPage, total)
    }
})

const kitPagination = computed(() => {
    const total = filteredKitItems.value.length
    const totalPages = Math.ceil(total / kitFilters.perPage)
    return {
        total,
        totalPages,
        currentPage: kitFilters.page,
        perPage: kitFilters.perPage,
        start: (kitFilters.page - 1) * kitFilters.perPage + 1,
        end: Math.min(kitFilters.page * kitFilters.perPage, total)
    }
})

const componentPagination = computed(() => {
    const total = filteredComponentItems.value.length
    const totalPages = Math.ceil(total / componentFilters.perPage)
    return {
        total,
        totalPages,
        currentPage: componentFilters.page,
        perPage: componentFilters.perPage,
        start: (componentFilters.page - 1) * componentFilters.perPage + 1,
        end: Math.min(componentFilters.page * componentFilters.perPage, total)
    }
})

// Computed para items disponibles para asignación
const availableItemsForAssignment = computed(() => {
    if (assignmentType.value === 'element') {
        // Para componentes, mostrar elementos disponibles (solo activos)
        return props.elementItems.filter(item => item.active)
    } else if (assignmentType.value === 'component') {
        // Para kits, mostrar componentes y elementos disponibles (solo activos)
        return [...props.componentItems, ...props.elementItems].filter(item => item.active)
    }
    return []
})

// Computed para contar filtros activos
const getActiveElementFiltersCount = computed(() => {
    let count = 0
    if (elementFilters.search) count++
    if (elementFilters.category) count++
    if (elementFilters.stock) count++
    if (elementFilters.status) count++
    return count
})

const getActiveKitFiltersCount = computed(() => {
    let count = 0
    if (kitFilters.search) count++
    if (kitFilters.category) count++
    if (kitFilters.stock) count++
    if (kitFilters.status) count++
    return count
})

const getActiveComponentFiltersCount = computed(() => {
    let count = 0
    if (componentFilters.search) count++
    if (componentFilters.category) count++
    if (componentFilters.stock) count++
    if (componentFilters.status) count++
    return count
})

// Funciones de filtrado y ordenamiento
function filterItems(items, filters, sort) {
    let filtered = items.filter(item => {
        // Filtro de búsqueda (nombre, descripción, categoría)
        const matchesSearch = !filters.search ||
            item.name.toLowerCase().includes(filters.search.toLowerCase()) ||
            (item.description && item.description.toLowerCase().includes(filters.search.toLowerCase())) ||
            (item.categoryName && item.categoryName.toLowerCase().includes(filters.search.toLowerCase()))

        // Filtro de categoría
        const matchesCategory = !filters.category || item.categoryName === filters.category

        // Filtro de stock usando la misma lógica que getStatusText
        let matchesStock = true
        if (filters.stock) {
            const current = parseFloat(item.current_stock)
            const min = parseFloat(item.min_stock)
            const max = parseFloat(item.max_stock)

            switch (filters.stock) {
                case 'bajo_stock':
                    matchesStock = current < min
                    break
                case 'en_minimo':
                    matchesStock = current === min
                    break
                case 'normal':
                    matchesStock = current > min && current <= max
                    break
                case 'sobre_stock':
                    matchesStock = current > max
                    break
                default:
                    matchesStock = true
            }
        }

        // Filtro de estado activo/inactivo
        let matchesStatus = true
        if (filters.status) {
            switch (filters.status) {
                case 'active':
                    matchesStatus = item.active === true
                    break
                case 'inactive':
                    matchesStatus = item.active === false
                    break
                default:
                    matchesStatus = true
            }
        }

        return matchesSearch && matchesCategory && matchesStock && matchesStatus
    })

    // Aplicar ordenamiento
    if (sort.field) {
        filtered.sort((a, b) => {
            let aValue = a[sort.field]
            let bValue = b[sort.field]

            if (aValue === null || aValue === undefined) aValue = ''
            if (bValue === null || bValue === undefined) bValue = ''

            // Campos numéricos que necesitan conversión especial
            const numericFields = ['current_stock', 'min_stock', 'max_stock', 'purchase_cost', 'sale_price']

            if (numericFields.includes(sort.field)) {
                // Convertir a números para ordenamiento numérico correcto
                const aNum = parseFloat(aValue) || 0
                const bNum = parseFloat(bValue) || 0
                const comparison = aNum - bNum
                return sort.direction === 'asc' ? comparison : -comparison
            } else if (typeof aValue === 'string' && typeof bValue === 'string') {
                // Ordenamiento alfabético para strings
                const comparison = aValue.toLowerCase().localeCompare(bValue.toLowerCase())
                return sort.direction === 'asc' ? comparison : -comparison
            } else {
                // Ordenamiento numérico directo
                const comparison = aValue - bValue
                return sort.direction === 'asc' ? comparison : -comparison
            }
        })
    }

    return filtered
}

// Funciones para limpiar filtros
function clearElementFilters() {
    elementFilters.search = ''
    elementFilters.category = ''
    elementFilters.stock = ''
    elementFilters.status = ''
    elementFilters.page = 1
}

function clearKitFilters() {
    kitFilters.search = ''
    kitFilters.category = ''
    kitFilters.stock = ''
    kitFilters.status = ''
    kitFilters.page = 1
}

function clearComponentFilters() {
    componentFilters.search = ''
    componentFilters.category = ''
    componentFilters.stock = ''
    componentFilters.status = ''
    componentFilters.page = 1
}

// Funciones de paginación
function goToElementPage(page) {
    if (page >= 1 && page <= elementPagination.value.totalPages) {
        elementFilters.page = page
    }
}

function goToKitPage(page) {
    if (page >= 1 && page <= kitPagination.value.totalPages) {
        kitFilters.page = page
    }
}

function goToComponentPage(page) {
    if (page >= 1 && page <= componentPagination.value.totalPages) {
        componentFilters.page = page
    }
}

// Funciones de ordenamiento
function sortElements(field) {
    if (elementSort.field === field) {
        elementSort.direction = elementSort.direction === 'asc' ? 'desc' : 'asc'
    } else {
        elementSort.field = field
        elementSort.direction = 'asc'
    }
    elementFilters.page = 1 // Reset a la primera página al ordenar
}

function sortKits(field) {
    if (kitSort.field === field) {
        kitSort.direction = kitSort.direction === 'asc' ? 'desc' : 'asc'
    } else {
        kitSort.field = field
        kitSort.direction = 'asc'
    }
    kitFilters.page = 1 // Reset a la primera página al ordenar
}

function sortComponents(field) {
    if (componentSort.field === field) {
        componentSort.field = field
        componentSort.direction = 'asc'
    } else {
        componentSort.field = field
        componentSort.direction = 'asc'
    }
    componentFilters.page = 1 // Reset a la primera página al ordenar
}

const availableElements = ref([])

// Funciones de modal para items
async function openModal(type, action, item = null) {
    currentItemType.value = type
    itemModalAction.value = action
    
    // Si estamos editando un componente o kit, cargar sus elementos asignados ANTES de mostrar el modal
    if (action === 'edit' && item && (type === 'component' || type === 'kit')) {
        try {
            const response = await axios.get(route('items.components', item.id))
            // Crear una copia del item con los elementos asignados
            selectedItem.value = {
                ...item,
                assignedElements: response.data
            }
        } catch (error) {
            console.error('Error loading assigned elements:', error)
            selectedItem.value = {
                ...item,
                assignedElements: []
            }
        }
    } else {
        selectedItem.value = item
    }
    
    showItemModal.value = true
}

function closeItemModal() {
    showItemModal.value = false
    selectedItem.value = null
}

// Función para abrir modal de asignación
async function openAssignmentModal(type, item) {

    currentItem.value = item
    assignmentType.value = type

    // Cargar items asignados desde el backend
    try {
        const response = await axios.get(route('items.components', item.id))
        assignedItems.value = response.data
    } catch (error) {
        console.error('Error loading assigned items:', error)
        assignedItems.value = []
    }

    showAssignmentModal.value = true
}

function closeAssignmentModal() {
    showAssignmentModal.value = false
    currentItem.value = null
    assignmentType.value = ''
    assignedItems.value = []
}


async function removeAssignment(itemId) {
    try {
        const response = await axios.delete(route('items.unassign', [currentItem.value.id, itemId]))

        if (response.status === 200) {
            // Remover de la lista local
            const index = assignedItems.value.findIndex(item => item.id === itemId)
            if (index !== -1) {
                assignedItems.value.splice(index, 1)
            }

            await Swal.fire({
                title: '¡Desasignado!',
                text: 'El componente ha sido desasignado correctamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            })
        }
    } catch (error) {
        console.error('Error removing assignment:', error)
        await Swal.fire({
            title: 'Error',
            text: error.response?.data?.message || 'No se pudo desasignar el componente.',
            icon: 'error'
        })
    }
}

// Funciones de modal para categorías
function openCategoryModal(type, action, category = null) {

    currentCategoryType.value = type // element, kit, component
    categoryModalType.value = action // add, edit
    selectedCategory.value = category
    showCategoryModal.value = true

}

function closeCategoryModal() {
    showCategoryModal.value = false
    selectedCategory.value = null
}

// Funciones de eliminación
function confirmDelete(type, item) {
    deleteModalType.value = type
    itemToDelete.value = item
    showDeleteModal.value = true
}

async function deleteItem() {
    try {
        const response = await axios.delete(route('items.destroy', itemToDelete.value.id))

        if (response.status === 200) {
            // Remover el item del estado local
            const itemType = deleteModalType.value
            const itemsArray = getItemsByType(itemType)
            const index = itemsArray.findIndex(item => item.id === itemToDelete.value.id)
            if (index !== -1) {
                itemsArray.splice(index, 1)
            }

            showDeleteModal.value = false
            itemToDelete.value = null
            await Swal.fire({
                title: '¡Eliminado!',
                text: 'El item ha sido eliminado correctamente',
                icon: 'success'
            })
        }
    } catch (error) {
        console.error('Error deleting item:', error)
        Swal.fire({
            title: 'Error',
            text: 'Error al eliminar el item error: ' + error,
            icon: 'error'
        })
    }
}

// Función para cambiar estado activo/inactivo
async function toggleItemActive(type, item) {
    try {

        const response = await axios.post(route('items.toggle-active', item.id))

        if (response.status === 200) {

            // Actualizar el estado local
            item.active = response.data.item.active

            // Mostrar mensaje de éxito
            const statusText = item.active ? 'activado' : 'desactivado'
            await Swal.fire({
                title: '¡Actualizado!',
                text: `El item ha sido ${statusText} correctamente.`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            })
        }
    } catch (error) {
        console.error('Error updating item status:', error)
        await Swal.fire({
            title: 'Error',
            text: error.response?.data?.message || 'No se pudo cambiar el estado del item.',
            icon: 'error'
        })
    }
}

// Función para ver detalles del item
function viewItem(item) {
    router.visit(route('items.show', item.id))
}

// Funciones de envío
async function handleItemSubmit(formData) {
    try {
        let response
        const itemType = currentItemType.value

        if (itemModalAction.value === 'add') {
            response = await axios.post(route('items.store'), {
                ...formData,
                type: itemType
            })

            if (response.status === 201) {
                // Agregar el nuevo item al estado local
                const newItem = response.data.item
                // Agregar información adicional para la vista
                newItem.categoryName = getCategoriesByType(itemType).find(cat => cat.id === newItem.category_id)?.name || 'Sin categoría'
                newItem.assignedTo = null

                // Procesar elementos asignados si existen
                if (formData.assignedElements && formData.assignedElements.length > 0) {
                    newItem.assignedElements = formData.assignedElements
                }

                switch (itemType) {
                    case 'element':
                        props.elementItems.push(newItem)
                        break
                    case 'kit':
                        props.kitItems.push(newItem)
                        break
                    case 'component':
                        props.componentItems.push(newItem)
                        break
                }
            }
        } else if (itemModalAction.value === 'edit') {

            response = await axios.put(route('items.update', selectedItem.value.id), formData)

            if (response.status === 200) {
                // Actualizar el item existente en el estado local
                const updatedItem = response.data.item
                updatedItem.categoryName = getCategoriesByType(itemType).find(cat => cat.id === updatedItem.category_id)?.name || 'Sin categoría'

                // Procesar elementos asignados si existen
                if (formData.assignedElements && formData.assignedElements.length > 0) {
                    updatedItem.assignedElements = formData.assignedElements
                }

                const itemsArray = getItemsByType(itemType)
                const index = itemsArray.findIndex(item => item.id === updatedItem.id)
                if (index !== -1) {
                    itemsArray[index] = updatedItem
                }
            }
        } else if (itemModalAction.value === 'assign') {
            response = await axios.put(route('items.update', selectedItem.value.id), {
                ...selectedItem.value,
                assignedTo: formData.assignedTo,
                assignedElements: formData.assignedElements || []
            })

            if (response.status === 200) {
                // Actualizar la asignación en el estado local
                const updatedItem = response.data.item
                const itemsArray = getItemsByType(itemType)
                const index = itemsArray.findIndex(item => item.id === updatedItem.id)
                if (index !== -1) {
                    itemsArray[index].assignedTo = updatedItem.assignedTo
                    itemsArray[index].assignedElements = formData.assignedElements || []
                }
            }
        }

        if (response.status === 200 || response.status === 201) {
            closeItemModal()
            await Swal.fire({
                title: '¡Éxito!',

                showConfirmButton: true,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#3085d6',
                timer: 4000,
                timerProgressBar: true,
                showCloseButton: true,

                text: itemModalAction.value === 'add' ? 'Item creado correctamente' :
                    itemModalAction.value === 'edit' ? 'Item actualizado correctamente' : 'Item asignado correctamente',
                icon: 'success'
            })
        }
    } catch (error) {
        console.error('Error submitting item:', error)
        console.error('Error response:', error.response?.data)
        console.error('Error status:', error.response?.status)

        let errorMessage = 'Error al procesar el item'

        if (error.response?.data?.message) {
            errorMessage = error.response.data.message
        } else if (error.response?.data?.errors) {
            errorMessage = 'Error de validación: ' + Object.values(error.response.data.errors).flat().join(', ')
        } else if (error.message) {
            errorMessage = error.message
        }

        Swal.fire({
            title: 'Error',
            text: errorMessage,
            icon: 'error'
        })
    }
}

async function handleCategorySubmit(eventData) {
    try {
        // eventData contiene: { action, category, type }
        const { action, category, type } = eventData


        if (action === 'add') {
            // Agregar la nueva categoría al estado local
            switch (type) {
                case 'element':
                    props.elementCategories.push(category)
                    break
                case 'kit':
                    props.kitCategories.push(category)
                    break
                case 'component':
                    props.componentCategories.push(category)
                    break
            }
        } else if (action === 'edit') {
            // Actualizar la categoría existente en el estado local
            const categoriesArray = getCategoriesByType(type)
            const index = categoriesArray.findIndex(cat => cat.id === category.id)
            if (index !== -1) {
                categoriesArray[index] = category
            }
        } else if (action === 'delete') {
            // Remover la categoría del estado local
            const categoriesArray = getCategoriesByType(type)
            const index = categoriesArray.findIndex(cat => cat.id === category.id)
            if (index !== -1) {
                categoriesArray.splice(index, 1)
            }
        }

        // Cerrar el modal después de procesar
        closeCategoryModal()

    } catch (error) {
        console.error('Error handling category event:', error)
        Swal.fire({
            title: 'Error',
            text: 'Error al procesar la categoría',
            icon: 'error'
        })
    }
}

// Funciones auxiliares
function getCategoriesByType(type) {
    switch (type) {
        case 'element': return props.elementCategories
        case 'kit': return props.kitCategories
        case 'component': return props.componentCategories
        default: return []
    }
}

function getItemsByType(type) {
    switch (type) {
        case 'element': return props.elementItems
        case 'kit': return props.kitItems
        case 'component': return props.componentItems
        default: return []
    }
}

// Función para contar estados del stock
function getStockStatusCount(status) {
    let count = 0

    // Función helper para calcular el estado del stock
    const getItemStockStatus = (item) => {
        // Convertir a números para comparaciones correctas
        const current = parseFloat(item.current_stock)
        const min = parseFloat(item.min_stock)
        const max = parseFloat(item.max_stock)

        if (current < min) {
            return 'bajo_stock'
        } else if (current === min) {
            return 'en_minimo'
        } else if (current > max) {
            return 'sobre_stock'
        } else {
            return 'normal'
        }
    }

    // Contar en elementos
    count += props.elementItems.filter(item => getItemStockStatus(item) === status).length

    // Contar en kits
    count += props.kitItems.filter(item => getItemStockStatus(item) === status).length

    // Contar en componentes
    count += props.componentItems.filter(item => getItemStockStatus(item) === status).length

    return count
}

// Inicialización
// Función para resetear página cuando cambien los filtros
function resetPageOnFilterChange(filterType, field) {
    if (filterType === 'element') {
        elementFilters.page = 1
    } else if (filterType === 'kit') {
        kitFilters.page = 1
    } else if (filterType === 'component') {
        componentFilters.page = 1
    }
}

// Función para verificar si se puede crear un item (requiere categorías)
function canCreateItem(type) {
    const categories = getCategoriesByType(type)
    return categories && categories.length > 0
}

// Función para obtener el tooltip del botón según el estado
function getButtonTooltip(type) {
    const categories = getCategoriesByType(type)
    if (!categories || categories.length === 0) {
        return `Primero debes crear al menos una categoría para ${type}s`
    }
    
    const typeNames = {
        element: 'elemento',
        component: 'componente', 
        kit: 'kit'
    }
    
    return `Crear un nuevo ${typeNames[type] || type}`
}

// Función para manejar el click del botón "Nuevo Item"
function handleNewItemClick(type) {
    if (canCreateItem(type)) {
        openModal(type, 'add')
    } else {
        Swal.fire({
            title: '¡Atención!',
            text: `Primero debes crear al menos una categoría para ${type}s`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Crear Categoría',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                openCategoryModal(type, 'add')
            }
        })
    }
}

// Funciones de Exportación e Importación
function exportItems(type) {
    // Usar route() helper de Laravel que respeta APP_URL y subdirectorios
    window.location.href = route('inventory-excel.export', { type: type })
}

function openImportModal(type) {
    importType.value = type
    showImportModal.value = true
}

function closeImportModal() {
    showImportModal.value = false
}

async function handleImportSuccess(results) {
    // Recargar la página para obtener los datos actualizados
    router.reload({ only: ['elementItems', 'kitItems', 'componentItems'] })

    closeImportModal()

    // Mostrar notificación de éxito
    await Swal.fire({
        title: '¡Importación Exitosa!',
        html: `
            <div class="text-left space-y-2">
                <p><strong>Creados:</strong> ${results.created}</p>
                <p><strong>Actualizados:</strong> ${results.updated}</p>
                <p><strong>Omitidos:</strong> ${results.skipped}</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#10b981',
        width: '400px',
        padding: '2rem',
        customClass: {
            popup: 'swal-import-success',
            confirmButton: 'px-6 py-3'
        }
    })
}

onMounted(() => {
    // Los datos ya vienen del backend, no necesitamos cargar nada adicional
})
</script>

<style scoped>  

.swal2-confirm {
  background-color: #80aa40 !important;
  color: #fff !important;
}

.swal2-confirm:hover {
  cursor: pointer;
  background-color: #4c6a22 !important; /* un azul más oscuro */
  color: #fff !important;
}

</style>