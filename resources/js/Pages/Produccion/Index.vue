<script setup>
import { ref, defineAsyncComponent } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
// Lazy loading para mejorar performance
const ProductionModals = defineAsyncComponent(() => import('@/Components/ProductionModals.vue'))
const InventoryOutputModals = defineAsyncComponent(() => import('@/Components/InventoryOutputModals.vue'))
import { PlusIcon, ComponentIcon, PackageIcon, EarthIcon, HandCoins } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const modals = ref(null)
const outputModals = ref(null)

const showSuccessMessage = (message) => {
    // Aquí puedes implementar tu sistema de notificaciones
    console.log('Success:', message)
    // Por ejemplo, usando SweetAlert2 si está disponible
    Swal.fire({
        title: '¡Éxito!',
        text: message,
        icon: 'success',
        timer: 3000,
        showConfirmButton: false
    })
}
</script>

<template>
    <AppLayout title="Produccion">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Producción - Gestión de Inventario
            </h2>
        </template>

        <Container class="">

            <!-- Botones de Acciones Principales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Agregar Elementos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <PlusIcon class="h-6 w-6 text-green-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Agregar Elemento</h3>
                            <p class="text-sm text-gray-500">Elementos individuales al inventario</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">
                        Agregue elementos individuales especificando el proveedor y costo de compra.
                        Incrementa el stock del elemento seleccionado.
                    </p>

                    <button 
                        @click="modals?.openElementModal()"
                        class="flex justify-center items-center py-4 rounded-md w-full text-white bg-green-500 hover:bg-green-700"
                    >
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Agregar Elemento
                    </button>
                </div>

                <!-- Crear Componente -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <ComponentIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Crear Componente</h3>
                            <p class="text-sm text-gray-500">Ensamblar desde elementos</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">
                        Cree componentes utilizando elementos existentes en inventario.
                        Resta elementos y aumenta componentes automáticamente.
                    </p>
                    <button
                        @click="modals?.openComponentModal()"
                        class="w-full flex items-center justify-center  py-4  rounded-md  text-white bg-blue-600 hover:bg-blue-700"
                    >
                        <ComponentIcon class="h-4 w-4 mr-2" />
                        Crear Componente
                    </button>
                </div>

                <!-- Ensamblar Kit -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 p-3 rounded-full mr-4">
                            <PackageIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Ensamblar Kit</h3>
                            <p class="text-sm text-gray-500">Combinar elementos y componentes</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">
                        Ensamble kits utilizando elementos y/o componentes disponibles.
                        Resta componentes y aumenta kits automáticamente.
                    </p>
                    <button 
                        @click="modals?.openKitModal()"
                        class="w-full flex items-center justify-center  py-4  rounded-md text-white bg-purple-600 hover:bg-purple-700"
                    >
                        <PackageIcon class="h-4 w-4 mr-2" />
                        Ensamblar Kit
                    </button>
                </div>
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-200 my-8"></div>

            <!-- Sección de Salidas de Inventario -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Salidas de Inventario
                </h3>
                <p class="text-gray-600 text-sm mb-6">
                    Registre las diferentes formas de salida del inventario con seguimiento completo de movimientos.
                </p>
            </div>

            <!-- Botones de Salidas -->
            <div class="grid grid-cols-1 md:grid-cols-3  gap-4 mb-8">
                <!-- Defectuoso -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-red-100 p-2 rounded-full mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-2">Defectuoso</h4>
                        <p class="text-xs text-gray-500 mb-3">Productos con fallas de fábrica</p>
                        <button
                            @click="outputModals?.openDefectiveModal()"
                            class="w-full flex items-center justify-center  rounded-md text-white bg-red-600 hover:bg-red-700 text-xs py-2"
                        >
                            Marcar
                        </button>
                    </div>
                </div>

                <!-- Dañado -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-orange-100 p-2 rounded-full mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-2">Dañado</h4>
                        <p class="text-xs text-gray-500 mb-3">Productos deteriorados</p>
                        <button
                            @click="outputModals?.openDamagedModal()"
                            class="w-full flex items-center justify-center  rounded-md text-white bg-orange-600 hover:bg-orange-700 text-xs py-2"
                        >
                            Marcar
                        </button>
                    </div>
                </div>

                <!-- Devolución -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-yellow-100 p-2 rounded-full mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-2">Devolución</h4>
                        <p class="text-xs text-gray-500 mb-3">Productos devueltos</p>
                        <button
                            @click="outputModals?.openReturnModal()"
                            class="w-full flex items-center justify-center  rounded-md text-white bg-yellow-600 hover:bg-yellow-700 text-xs py-2"
                        >
                            Registrar
                        </button>
                    </div>
                </div>

                
            </div>

            <!-- Segunda fila de botones de venta -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <!-- Venta General -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-green-100 p-2 rounded-full mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                            <HandCoins class="h-6 w-6 text-green-600" />
                        </div>
                        <h4 class="font-medium text-gray-900 mb-2">Venta</h4>
                        <p class="text-xs text-gray-500 mb-3">Venta directa</p>
                        <button
                            @click="outputModals?.openSaleModal()"
                            class="w-full flex items-center justify-center  rounded-md text-white bg-green-600 hover:bg-green-700 text-xs py-2"
                        >
                            Registrar
                        </button>
                    </div>
                </div>
                <!-- MercadoLibre -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-yellow-100 p-2 rounded-full mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900 mb-2">MercadoLibre</h4>
                        <p class="text-xs text-gray-500 mb-3">Ventas ML</p>
                        <button
                            @click="outputModals?.openMercadoLibreModal()"
                            class="w-full flex items-center justify-center  rounded-md text-white bg-yellow-500 hover:bg-yellow-600 text-xs py-2"
                        >
                            Registrar
                        </button>
                    </div>
                </div>

                <!-- Página Web -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-purple-100 p-2 rounded-full mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                            <EarthIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <h4 class="font-medium text-gray-900 mb-2">Página Web</h4>
                        <p class="text-xs text-gray-500 mb-3">Sitio web propio</p>
                        <button
                            @click="outputModals?.openWebsiteModal()"
                            class="w-full flex items-center justify-center  rounded-md text-white bg-purple-600 hover:bg-purple-700 text-xs py-2"
                        >
                            Registrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h4 class="text-md font-medium text-gray-900 mb-3">
                    Información sobre el Sistema de Inventario
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <h5 class="font-medium text-gray-800 mb-2">Elementos</h5>
                        <p class="text-gray-600">
                            Son las piezas básicas del inventario. Al agregarlos, debe especificar
                            el proveedor y el costo de compra para mantener un registro completo.
                        </p>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-800 mb-2">Componentes</h5>
                        <p class="text-gray-600">
                            Se crean a partir de elementos existentes. El sistema verifica
                            automáticamente que hay suficiente stock antes de permitir el ensamblaje.
                        </p>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-800 mb-2">Kits</h5>
                        <p class="text-gray-600">
                            Conjuntos complejos que pueden incluir tanto elementos como componentes.
                            Perfectos para productos finales o agrupaciones especializadas.
                        </p>
                    </div>
                </div>
            </div>
        </Container>


        <!-- Modales -->
        <ProductionModals 
            ref="modals"
            @success="showSuccessMessage"
        />
        
        <!-- Modales de Salida -->
        <InventoryOutputModals 
            ref="outputModals"
            @success="showSuccessMessage"
        />
    </AppLayout>
</template>
