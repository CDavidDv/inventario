<template>
    <teleport to="body">
        <transition name="modal">
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <!-- Overlay -->
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>

                    <!-- Modal -->
                    <div class="relative inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-gray-900">
                                Importar {{ typeLabel }}
                            </h3>
                            <button @click="close" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <X class="w-6 h-6" />
                            </button>
                        </div>

                        <!-- Tabs -->
                        <div class="mb-6">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8">
                                    <button @click="activeTab = 'upload'" :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                        activeTab === 'upload'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ]">
                                        <Upload class="w-5 h-5 inline mr-2" />
                                        Subir Archivo
                                    </button>
                                    <button @click="activeTab = 'preview'" :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                        activeTab === 'preview'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ]" :disabled="!file">
                                        <Eye class="w-5 h-5 inline mr-2" />
                                        Vista Previa
                                    </button>
                                    <button @click="activeTab = 'results'" :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                        activeTab === 'results'
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ]" :disabled="!importResults">
                                        <CheckCircle class="w-5 h-5 inline mr-2" />
                                        Resultados
                                    </button>
                                </nav>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="min-h-[400px]">
                            <!-- Upload Tab -->
                            <div v-show="activeTab === 'upload'" class="space-y-6">
                                <!-- File Upload Area -->
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors"
                                     @dragover.prevent="isDragging = true"
                                     @dragleave.prevent="isDragging = false"
                                     @drop.prevent="handleDrop">
                                    <input type="file" ref="fileInput" @change="handleFileSelect" accept=".xlsx,.xls,.csv" class="hidden" />

                                    <div v-if="!file" class="space-y-4">
                                        <FileSpreadsheet class="w-16 h-16 mx-auto text-gray-400" />
                                        <div>
                                            <p class="text-lg font-medium text-gray-700">
                                                Arrastra y suelta tu archivo aquí
                                            </p>
                                            <p class="text-sm text-gray-500 mt-2">
                                                o
                                            </p>
                                        </div>
                                        <button @click="$refs.fileInput.click()"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                            Seleccionar Archivo
                                        </button>
                                        <p class="text-xs text-gray-500">
                                            Formatos soportados: .xlsx, .xls, .csv (Máx. 10MB)
                                        </p>
                                    </div>

                                    <div v-else class="space-y-4">
                                        <CheckCircle class="w-16 h-16 mx-auto text-green-500" />
                                        <div>
                                            <p class="text-lg font-medium text-gray-700">{{ file.name }}</p>
                                            <p class="text-sm text-gray-500">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                        <button @click="removeFile"
                                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                            <Trash2 class="w-4 h-4 inline mr-2" />
                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <!-- Instructions -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                                        <Info class="w-5 h-5 mr-2" />
                                        Instrucciones
                                    </h4>
                                    <ul class="text-sm text-blue-800 space-y-1 ml-7 list-disc">
                                        <li>Si incluyes el ID del item, se actualizará ese item existente</li>
                                        <li>Si no incluyes el ID pero el nombre coincide, se actualizará el item</li>
                                        <li>Si el item no existe, se creará uno nuevo</li>
                                        <li>Las categorías nuevas se crearán automáticamente</li>
                                        <li>Los nombres de categorías se normalizarán para evitar duplicados</li>
                                    </ul>
                                </div>

                                <!-- Download Template Button -->
                                <div class="flex justify-center">
                                    <button @click="downloadTemplate"
                                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                                        <Download class="w-5 h-5" />
                                        Descargar Plantilla de {{ typeLabel }}
                                    </button>
                                </div>
                            </div>

                            <!-- Preview Tab -->
                            <div v-show="activeTab === 'preview'" class="space-y-4">
                                <div v-if="isLoadingPreview" class="flex justify-center items-center h-64">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                                </div>

                                <div v-else-if="previewData" class="space-y-4">
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <p class="text-sm text-yellow-800">
                                            <strong>Total de filas:</strong> {{ previewData.total_rows }}
                                            <br />
                                            <strong>Mostrando:</strong> Primeras {{ previewData.preview.length - 1 }} filas
                                        </p>
                                    </div>

                                    <div class="overflow-x-auto border rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th v-for="(header, index) in previewData.preview[0]" :key="index"
                                                        class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ header }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="(row, rowIndex) in previewData.preview.slice(1)" :key="rowIndex">
                                                    <td v-for="(cell, cellIndex) in row" :key="cellIndex"
                                                        class="px-3 py-2 text-sm text-gray-900 whitespace-nowrap">
                                                        {{ cell }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div v-else class="text-center text-gray-500 py-12">
                                    No hay datos para previsualizar
                                </div>
                            </div>

                            <!-- Results Tab -->
                            <div v-show="activeTab === 'results'" class="space-y-4">
                                <div v-if="importResults" class="space-y-4">
                                    <!-- Summary Cards -->
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                            <div class="text-3xl font-bold text-green-600">{{ importResults.created }}</div>
                                            <div class="text-sm text-green-800">Creados</div>
                                        </div>
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                            <div class="text-3xl font-bold text-blue-600">{{ importResults.updated }}</div>
                                            <div class="text-sm text-blue-800">Actualizados</div>
                                        </div>
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                            <div class="text-3xl font-bold text-yellow-600">{{ importResults.skipped }}</div>
                                            <div class="text-sm text-yellow-800">Omitidos</div>
                                        </div>
                                    </div>

                                    <!-- Errors List -->
                                    <div v-if="importResults.errors && importResults.errors.length > 0"
                                         class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-red-900 mb-3 flex items-center">
                                            <AlertCircle class="w-5 h-5 mr-2" />
                                            Errores encontrados ({{ importResults.errors.length }})
                                        </h4>
                                        <div class="max-h-64 overflow-y-auto space-y-2">
                                            <div v-for="(error, index) in importResults.errors" :key="index"
                                                 class="bg-white rounded p-3 text-sm">
                                                <p class="font-semibold text-red-800">Fila {{ error.row }}:</p>
                                                <ul class="list-disc list-inside text-red-700 ml-4">
                                                    <li v-for="(err, errIndex) in error.errors" :key="errIndex">{{ err }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                        <CheckCircle class="w-12 h-12 mx-auto text-green-500 mb-2" />
                                        <p class="text-green-800 font-semibold">
                                            ¡Importación completada exitosamente sin errores!
                                        </p>
                                    </div>
                                </div>

                                <div v-else class="text-center text-gray-500 py-12">
                                    No hay resultados para mostrar
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-8 flex justify-end gap-3">
                            <button @click="close"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                                {{ importResults ? 'Cerrar' : 'Cancelar' }}
                            </button>
                            <button v-if="file && !importResults"
                                    @click="loadPreview"
                                    :disabled="isLoadingPreview"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <Eye class="w-4 h-4 inline mr-2" />
                                Ver Vista Previa
                            </button>
                            <button v-if="file && !importResults"
                                    @click="processImport"
                                    :disabled="isProcessing"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <Upload class="w-4 h-4 inline mr-2" />
                                {{ isProcessing ? 'Importando...' : 'Importar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { X, Upload, Download, Eye, CheckCircle, Info, Trash2, FileSpreadsheet, AlertCircle } from 'lucide-vue-next'
import axios from '@/axios-config'
import Swal from 'sweetalert2'

const props = defineProps({
    show: Boolean,
    type: String // 'all', 'element', 'component', 'kit'
})

const emit = defineEmits(['close', 'imported'])

const activeTab = ref('upload')
const file = ref(null)
const isDragging = ref(false)
const isLoadingPreview = ref(false)
const isProcessing = ref(false)
const previewData = ref(null)
const importResults = ref(null)

const typeLabel = computed(() => {
    const labels = {
        all: 'Inventario Completo',
        element: 'Elementos',
        component: 'Componentes',
        kit: 'Kits'
    }
    return labels[props.type] || 'Items'
})

watch(() => props.show, (newVal) => {
    if (newVal) {
        // Reset state when modal opens
        activeTab.value = 'upload'
        file.value = null
        previewData.value = null
        importResults.value = null
    }
})

const handleFileSelect = (event) => {
    const selectedFile = event.target.files[0]
    if (selectedFile) {
        validateAndSetFile(selectedFile)
    }
}

const handleDrop = (event) => {
    isDragging.value = false
    const droppedFile = event.dataTransfer.files[0]
    if (droppedFile) {
        validateAndSetFile(droppedFile)
    }
}

const validateAndSetFile = (selectedFile) => {
    const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv']
    const maxSize = 10 * 1024 * 1024 // 10MB

    if (!validTypes.includes(selectedFile.type)) {
        Swal.fire({
            title: 'Archivo inválido',
            text: 'Solo se permiten archivos Excel (.xlsx, .xls) o CSV',
            icon: 'error'
        })
        return
    }

    if (selectedFile.size > maxSize) {
        Swal.fire({
            title: 'Archivo muy grande',
            text: 'El archivo no debe exceder 10MB',
            icon: 'error'
        })
        return
    }

    file.value = selectedFile
    previewData.value = null
    importResults.value = null
}

const removeFile = () => {
    file.value = null
    previewData.value = null
    importResults.value = null
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const loadPreview = async () => {
    if (!file.value) return

    isLoadingPreview.value = true

    try {
        const formData = new FormData()
        formData.append('file', file.value)

        const response = await axios.post('/inventory-excel/preview', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        if (response.data.success) {
            previewData.value = response.data
            activeTab.value = 'preview'
        }
    } catch (error) {
        console.error('Error loading preview:', error)
        Swal.fire({
            title: 'Error',
            text: error.response?.data?.message || 'No se pudo cargar la vista previa',
            icon: 'error'
        })
    } finally {
        isLoadingPreview.value = false
    }
}

const processImport = async () => {
    if (!file.value) return

    isProcessing.value = true

    try {
        const formData = new FormData()
        formData.append('file', file.value)
        formData.append('type', props.type)

        const response = await axios.post('/inventory-excel/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        if (response.data.success) {
            importResults.value = response.data.results
            activeTab.value = 'results'

            Swal.fire({
                title: '¡Importación Completada!',
                html: `
                    <p><strong>Creados:</strong> ${response.data.results.created}</p>
                    <p><strong>Actualizados:</strong> ${response.data.results.updated}</p>
                    <p><strong>Omitidos:</strong> ${response.data.results.skipped}</p>
                `,
                icon: 'success',
                timer: 3000
            })

            emit('imported', response.data.results)
        }
    } catch (error) {
        console.error('Error importing file:', error)
        Swal.fire({
            title: 'Error en la Importación',
            text: error.response?.data?.message || 'No se pudo procesar el archivo',
            icon: 'error'
        })
    } finally {
        isProcessing.value = false
    }
}

const downloadTemplate = async () => {
    try {
        window.location.href = `/inventory-excel/template?type=${props.type}`
    } catch (error) {
        console.error('Error downloading template:', error)
        Swal.fire({
            title: 'Error',
            text: 'No se pudo descargar la plantilla',
            icon: 'error'
        })
    }
}

const close = () => {
    emit('close')
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
