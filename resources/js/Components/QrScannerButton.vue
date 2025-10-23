<template>
    <!-- Botón flotante - solo visible en móviles -->
    <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 md:hidden">
        <button
            @click="openScanner"
            class="bg-green-500 hover:bg-green-600 text-white rounded-full p-4 shadow-2xl transform transition-all duration-200 hover:scale-110 active:scale-95 flex items-center justify-center"
            :class="{ 'animate-pulse': isScanning }"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-8 w-8"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                />
            </svg>
        </button>
    </div>

    <!-- Modal del Scanner -->
    <Teleport to="body">
        <div
            v-if="showScanner"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-90"
        >
            <div class="relative w-full h-full max-w-2xl max-h-screen p-4">
                <!-- Botón cerrar -->
                <button
                    @click="closeScanner"
                    class="absolute top-6 right-6 z-10 bg-red-500 hover:bg-red-600 text-white rounded-full p-3 shadow-lg"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Scanner -->
                <div class="bg-white rounded-lg overflow-hidden h-full flex flex-col">
                    <div class="bg-green-600 text-white p-4 text-center">
                        <h2 class="text-xl font-bold">Escanear Código QR</h2>
                        <p class="text-sm mt-1">Coloca el código QR dentro del marco</p>
                    </div>

                    <div class="flex-1 relative bg-gray-900">
                        <QrcodeStream
                            v-if="showScanner"
                            @detect="onDecode"
                            @error="onError"
                            @camera-on="onCameraReady"
                            :track="paintBoundingBox"
                            class="w-full h-full"
                        >
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-64 h-64 border-4 border-green-500 rounded-lg"></div>
                            </div>
                        </QrcodeStream>

                        <!-- Loading overlay -->
                        <div
                            v-if="!cameraReady"
                            class="absolute inset-0 flex items-center justify-center bg-gray-900 bg-opacity-75"
                        >
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
                                <p class="text-white">Iniciando cámara...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Estado del scanner -->
                    <div class="p-4 bg-gray-50">
                        <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ errorMessage }}
                        </div>
                        <div v-else-if="scanResult" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            Escaneado: {{ scanResult }}
                        </div>
                        <div v-else class="text-gray-600 text-center text-sm">
                            <p>Esperando código QR...</p>
                            <p class="mt-1 text-xs">El QR debe contener: ID, Nombre, o combinaciones como "ID-Nombre"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { QrcodeStream } from 'vue-qrcode-reader'
import axios from '@/axios-config'
import Swal from 'sweetalert2'

const showScanner = ref(false)
const isScanning = ref(false)
const scanResult = ref('')
const errorMessage = ref('')
const cameraReady = ref(false)

const openScanner = () => {
    showScanner.value = true
    isScanning.value = true
    cameraReady.value = false
    errorMessage.value = ''
    scanResult.value = ''
}

const closeScanner = () => {
    showScanner.value = false
    isScanning.value = false
    cameraReady.value = false
    errorMessage.value = ''
    scanResult.value = ''
}

const onCameraReady = () => {
    cameraReady.value = true
}

const onError = (error) => {
    console.error('Camera error:', error)
    errorMessage.value = 'Error al acceder a la cámara. Por favor, verifica los permisos.'
}

const paintBoundingBox = (detectedCodes, ctx) => {
    for (const detectedCode of detectedCodes) {
        const {
            boundingBox: { x, y, width, height }
        } = detectedCode

        ctx.lineWidth = 2
        ctx.strokeStyle = '#00ff00'
        ctx.strokeRect(x, y, width, height)
    }
}

const onDecode = async (result) => {
    if (!result || result.length === 0) return

    const qrContent = result[0].rawValue
    scanResult.value = qrContent

    // Buscar el item usando route() helper de Laravel
    try {
        const response = await axios.get(route('api.inventory.search-by-qr'), {
            params: { qr: qrContent }
        })

        if (response.data.success && response.data.item) {
            closeScanner()

            // Pequeño delay para cerrar el modal antes de navegar
            setTimeout(() => {
                // Usar route() helper que respeta el APP_URL base y subdirectorios
                router.visit(route('items.show', response.data.item.id))
            }, 300)
        } else {
            errorMessage.value = 'No se encontró ningún item con ese código QR'

            Swal.fire({
                title: 'Item no encontrado',
                text: `No se encontró ningún item con el código: ${qrContent}`,
                icon: 'error',
                confirmButtonText: 'Intentar de nuevo',
                confirmButtonColor: '#10b981'
            }).then(() => {
                // Reiniciar scanner
                scanResult.value = ''
                errorMessage.value = ''
            })
        }
    } catch (error) {
        console.error('Error searching item:', error)
        errorMessage.value = 'Error al buscar el item'

        Swal.fire({
            title: 'Error de escaneo',
            text: error.response?.data?.message || 'Error al buscar el item. Por favor, intenta de nuevo.',
            icon: 'error',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#ef4444',
            buttonsStyling: true,
        })
    }
}
</script>

<style scoped>
/* Animación personalizada para el botón */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .7;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
