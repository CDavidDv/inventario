<template>
    <!-- Modal para Marcar como Defectuoso -->
    <DialogModal :show="showDefectiveModal" @close="closeDefectiveModal">
        <template #title>
            Marcar como Defectuoso
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="defective_item_id" value="Item" />
                    <select 
                        id="defective_item_id" 
                        v-model="defectiveForm.item_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar item...</option>
                        <option 
                            v-for="item in allItems" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div>
                    <InputLabel for="defective_quantity" value="Cantidad" />
                    <TextInput
                        id="defective_quantity"
                        v-model="defectiveForm.quantity"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="mt-1 block w-full"
                        placeholder="Cantidad defectuosa"
                    />
                </div>

                <div>
                    <InputLabel for="defective_reason" value="Razón del Defecto" />
                    <select 
                        id="defective_reason" 
                        v-model="defectiveForm.reason"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar razón...</option>
                        <option value="fabricacion">Falla de fabricación</option>
                        <option value="material">Material defectuoso</option>
                        <option value="ensamblaje">Error de ensamblaje</option>
                        <option value="control_calidad">Rechazo control de calidad</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="defective_notes" value="Notas (Opcional)" />
                    <textarea
                        id="defective_notes"
                        v-model="defectiveForm.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Detalles adicionales..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeDefectiveModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 bg-red-600 hover:bg-red-700"
                :class="{ 'opacity-25': defectiveForm.processing }"
                :disabled="defectiveForm.processing"
                @click="submitDefectiveForm"
            >
                <span v-if="defectiveForm.processing">Marcando...</span>
                <span v-else>Marcar como Defectuoso</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Marcar como Dañado -->
    <DialogModal :show="showDamagedModal" @close="closeDamagedModal">
        <template #title>
            Marcar como Dañado
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="damaged_item_id" value="Item" />
                    <select 
                        id="damaged_item_id" 
                        v-model="damagedForm.item_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar item...</option>
                        <option 
                            v-for="item in allItems" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div>
                    <InputLabel for="damaged_quantity" value="Cantidad" />
                    <TextInput
                        id="damaged_quantity"
                        v-model="damagedForm.quantity"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="mt-1 block w-full"
                        placeholder="Cantidad dañada"
                    />
                </div>

                <div>
                    <InputLabel for="damage_type" value="Tipo de Daño" />
                    <select 
                        id="damage_type" 
                        v-model="damagedForm.damage_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar tipo...</option>
                        <option value="fisico">Daño físico</option>
                        <option value="agua">Daño por agua</option>
                        <option value="humedad">Daño por humedad</option>
                        <option value="calor">Daño por calor</option>
                        <option value="caida">Daño por caída</option>
                        <option value="manejo">Daño por mal manejo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="damaged_notes" value="Notas (Opcional)" />
                    <textarea
                        id="damaged_notes"
                        v-model="damagedForm.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Descripción del daño..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeDamagedModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 bg-orange-600 hover:bg-orange-700"
                :class="{ 'opacity-25': damagedForm.processing }"
                :disabled="damagedForm.processing"
                @click="submitDamagedForm"
            >
                <span v-if="damagedForm.processing">Marcando...</span>
                <span v-else>Marcar como Dañado</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Registrar Devolución -->
    <DialogModal :show="showReturnModal" @close="closeReturnModal">
        <template #title>
            Registrar Devolución
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="return_item_id" value="Item" />
                    <select 
                        id="return_item_id" 
                        v-model="returnForm.item_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar item...</option>
                        <option 
                            v-for="item in allItems" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div>
                    <InputLabel for="return_quantity" value="Cantidad" />
                    <TextInput
                        id="return_quantity"
                        v-model="returnForm.quantity"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="mt-1 block w-full"
                        placeholder="Cantidad devuelta"
                    />
                </div>

                <div>
                    <InputLabel for="return_reason" value="Razón de Devolución" />
                    <select 
                        id="return_reason" 
                        v-model="returnForm.return_reason"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar razón...</option>
                        <option value="no_satisface">No satisface expectativas</option>
                        <option value="defectuoso">Producto defectuoso</option>
                        <option value="dañado_envio">Dañado en envío</option>
                        <option value="incorrecto">Producto incorrecto</option>
                        <option value="cambio_opinion">Cambio de opinión</option>
                        <option value="garantia">Bajo garantía</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="customer_info" value="Información del Cliente" />
                    <TextInput
                        id="customer_info"
                        v-model="returnForm.customer_info"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Nombre, teléfono o información del cliente"
                    />
                </div>

                <div>
                    <InputLabel for="return_notes" value="Notas (Opcional)" />
                    <textarea
                        id="return_notes"
                        v-model="returnForm.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Detalles de la devolución..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeReturnModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 bg-yellow-600 hover:bg-yellow-700"
                :class="{ 'opacity-25': returnForm.processing }"
                :disabled="returnForm.processing"
                @click="submitReturnForm"
            >
                <span v-if="returnForm.processing">Registrando...</span>
                <span v-else>Registrar Devolución</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Venta General -->
    <DialogModal :show="showSaleModal" @close="closeSaleModal">
        <template #title>
            Registrar Venta
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="sale_item_id" value="Item" />
                    <select 
                        id="sale_item_id" 
                        v-model="saleForm.item_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar item...</option>
                        <option 
                            v-for="item in allItems" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div>
                    <InputLabel for="sale_quantity" value="Cantidad" />
                    <TextInput
                        id="sale_quantity"
                        v-model="saleForm.quantity"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="mt-1 block w-full"
                        placeholder="Cantidad vendida"
                    />
                </div>

                <div>
                    <InputLabel for="sale_price" value="Precio de Venta" />
                    <TextInput
                        id="sale_price"
                        v-model="saleForm.sale_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full"
                        placeholder="0.00"
                    />
                </div>

                <div>
                    <InputLabel for="sale_customer" value="Cliente" />
                    <TextInput
                        id="sale_customer"
                        v-model="saleForm.customer_info"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Nombre o información del cliente"
                    />
                </div>

                <div>
                    <InputLabel for="payment_method" value="Método de Pago" />
                    <select 
                        id="payment_method" 
                        v-model="saleForm.payment_method"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar método...</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="sale_notes" value="Notas (Opcional)" />
                    <textarea
                        id="sale_notes"
                        v-model="saleForm.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Detalles de la venta..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeSaleModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 bg-green-600 hover:bg-green-700"
                :class="{ 'opacity-25': saleForm.processing }"
                :disabled="saleForm.processing"
                @click="submitSaleForm"
            >
                <span v-if="saleForm.processing">Registrando...</span>
                <span v-else>Registrar Venta</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Venta por Internet -->
    <DialogModal :show="showInternetSaleModal" @close="closeInternetSaleModal">
        <template #title>
            Registrar Venta por Internet
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="internet_item_id" value="Item" />
                    <select 
                        id="internet_item_id" 
                        v-model="internetSaleForm.item_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar item...</option>
                        <option 
                            v-for="item in allItems" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="internet_quantity" value="Cantidad" />
                        <TextInput
                            id="internet_quantity"
                            v-model="internetSaleForm.quantity"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel for="internet_price" value="Precio" />
                        <TextInput
                            id="internet_price"
                            v-model="internetSaleForm.sale_price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                        />
                    </div>
                </div>

                <div>
                    <InputLabel for="platform" value="Plataforma" />
                    <select 
                        id="platform" 
                        v-model="internetSaleForm.platform"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar plataforma...</option>
                        <option value="facebook">Facebook Marketplace</option>
                        <option value="instagram">Instagram</option>
                        <option value="whatsapp">WhatsApp Business</option>
                        <option value="ebay">eBay</option>
                        <option value="amazon">Amazon</option>
                        <option value="otra">Otra plataforma</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="order_number" value="Número de Orden" />
                    <TextInput
                        id="order_number"
                        v-model="internetSaleForm.order_number"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="ID de la orden/pedido"
                    />
                </div>

                <div>
                    <InputLabel for="internet_customer" value="Cliente" />
                    <TextInput
                        id="internet_customer"
                        v-model="internetSaleForm.customer_info"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Información del cliente"
                    />
                </div>

                <div>
                    <InputLabel for="internet_notes" value="Notas" />
                    <textarea
                        id="internet_notes"
                        v-model="internetSaleForm.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Detalles de la venta..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeInternetSaleModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 bg-blue-600 hover:bg-blue-700"
                :class="{ 'opacity-25': internetSaleForm.processing }"
                :disabled="internetSaleForm.processing"
                @click="submitInternetSaleForm"
            >
                <span v-if="internetSaleForm.processing">Registrando...</span>
                <span v-else>Registrar Venta</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Venta MercadoLibre -->
    <DialogModal :show="showMercadoLibreModal" @close="closeMercadoLibreModal">
        <template #title>
            Registrar Venta MercadoLibre
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="ml_item_id" value="Item" />
                    <select 
                        id="ml_item_id" 
                        v-model="mercadoLibreForm.item_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar item...</option>
                        <option 
                            v-for="item in allItems" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="ml_quantity" value="Cantidad" />
                        <TextInput
                            id="ml_quantity"
                            v-model="mercadoLibreForm.quantity"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel for="ml_price" value="Precio" />
                        <TextInput
                            id="ml_price"
                            v-model="mercadoLibreForm.sale_price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                        />
                    </div>
                </div>

                <div>
                    <InputLabel for="ml_order_id" value="ID de Orden ML" />
                    <TextInput
                        id="ml_order_id"
                        v-model="mercadoLibreForm.ml_order_id"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Número de orden de MercadoLibre"
                    />
                </div>

                <div>
                    <InputLabel for="buyer_info" value="Comprador" />
                    <TextInput
                        id="buyer_info"
                        v-model="mercadoLibreForm.buyer_info"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Información del comprador"
                    />
                </div>

                <div>
                    <InputLabel for="shipping_method" value="Método de Envío" />
                    <select 
                        id="shipping_method" 
                        v-model="mercadoLibreForm.shipping_method"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar envío...</option>
                        <option value="mercado_envios">Mercado Envíos</option>
                        <option value="retiro_persona">Retiro en persona</option>
                        <option value="envio_propio">Envío propio</option>
                        <option value="correo">Correo Argentino</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="ml_notes" value="Notas" />
                    <textarea
                        id="ml_notes"
                        v-model="mercadoLibreForm.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Detalles de la venta..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeMercadoLibreModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 bg-yellow-500 hover:bg-yellow-600"
                :class="{ 'opacity-25': mercadoLibreForm.processing }"
                :disabled="mercadoLibreForm.processing"
                @click="submitMercadoLibreForm"
            >
                <span v-if="mercadoLibreForm.processing">Registrando...</span>
                <span v-else>Registrar Venta ML</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Venta Página Web -->
    <DialogModal :show="showWebsiteModal" @close="closeWebsiteModal">
        <template #title>
            Registrar Venta Página Web
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="web_item_id" value="Item" />
                    <select 
                        id="web_item_id" 
                        v-model="websiteForm.item_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar item...</option>
                        <option 
                            v-for="item in allItems" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="web_quantity" value="Cantidad" />
                        <TextInput
                            id="web_quantity"
                            v-model="websiteForm.quantity"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel for="web_price" value="Precio" />
                        <TextInput
                            id="web_price"
                            v-model="websiteForm.sale_price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                        />
                    </div>
                </div>

                <div>
                    <InputLabel for="web_order_number" value="Número de Orden" />
                    <TextInput
                        id="web_order_number"
                        v-model="websiteForm.order_number"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Número de orden del sitio web"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="customer_email" value="Email Cliente" />
                        <TextInput
                            id="customer_email"
                            v-model="websiteForm.customer_email"
                            type="email"
                            class="mt-1 block w-full"
                            placeholder="email@cliente.com"
                        />
                    </div>
                    <div>
                        <InputLabel for="customer_phone" value="Teléfono" />
                        <TextInput
                            id="customer_phone"
                            v-model="websiteForm.customer_phone"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Teléfono del cliente"
                        />
                    </div>
                </div>

                <div>
                    <InputLabel for="delivery_method" value="Método de Entrega" />
                    <select 
                        id="delivery_method" 
                        v-model="websiteForm.delivery_method"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar entrega...</option>
                        <option value="domicilio">Envío a domicilio</option>
                        <option value="retiro">Retiro en local</option>
                        <option value="punto_retiro">Punto de retiro</option>
                        <option value="mensajeria">Mensajería</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div>
                    <InputLabel for="web_notes" value="Notas" />
                    <textarea
                        id="web_notes"
                        v-model="websiteForm.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Detalles de la venta..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeWebsiteModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 bg-purple-600 hover:bg-purple-700"
                :class="{ 'opacity-25': websiteForm.processing }"
                :disabled="websiteForm.processing"
                @click="submitWebsiteForm"
            >
                <span v-if="websiteForm.processing">Registrando...</span>
                <span v-else>Registrar Venta Web</span>
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DialogModal from '@/Components/DialogModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import axios from 'axios'

const emit = defineEmits(['success'])

// Estados de los modales
const showDefectiveModal = ref(false)
const showDamagedModal = ref(false)
const showReturnModal = ref(false)
const showSaleModal = ref(false)
const showInternetSaleModal = ref(false)
const showMercadoLibreModal = ref(false)
const showWebsiteModal = ref(false)

// Datos
const allItems = ref([])

// Formularios
const defectiveForm = useForm({
    item_id: '',
    quantity: '',
    reason: '',
    notes: ''
})

const damagedForm = useForm({
    item_id: '',
    quantity: '',
    damage_type: '',
    notes: ''
})

const returnForm = useForm({
    item_id: '',
    quantity: '',
    return_reason: '',
    customer_info: '',
    notes: ''
})

const saleForm = useForm({
    item_id: '',
    quantity: '',
    sale_price: '',
    customer_info: '',
    payment_method: '',
    notes: ''
})

const internetSaleForm = useForm({
    item_id: '',
    quantity: '',
    sale_price: '',
    platform: '',
    order_number: '',
    customer_info: '',
    notes: ''
})

const mercadoLibreForm = useForm({
    item_id: '',
    quantity: '',
    sale_price: '',
    ml_order_id: '',
    buyer_info: '',
    shipping_method: '',
    notes: ''
})

const websiteForm = useForm({
    item_id: '',
    quantity: '',
    sale_price: '',
    order_number: '',
    customer_email: '',
    customer_phone: '',
    delivery_method: '',
    notes: ''
})

// Métodos de carga
const loadAllItems = async () => {
    try {
        const [elements, components, kits] = await Promise.all([
            axios.get('/production/api/items/element'),
            axios.get('/production/api/items/component'),
            axios.get('/production/api/items/kit')
        ])
        
        allItems.value = [
            ...elements.data,
            ...components.data,
            ...kits.data
        ].sort((a, b) => a.name.localeCompare(b.name))
    } catch (error) {
        console.error('Error loading items:', error)
    }
}

// Métodos de apertura de modales
const openDefectiveModal = async () => {
    await loadAllItems()
    showDefectiveModal.value = true
}

const openDamagedModal = async () => {
    await loadAllItems()
    showDamagedModal.value = true
}

const openReturnModal = async () => {
    await loadAllItems()
    showReturnModal.value = true
}

const openSaleModal = async () => {
    await loadAllItems()
    showSaleModal.value = true
}

const openInternetSaleModal = async () => {
    await loadAllItems()
    showInternetSaleModal.value = true
}

const openMercadoLibreModal = async () => {
    await loadAllItems()
    showMercadoLibreModal.value = true
}

const openWebsiteModal = async () => {
    await loadAllItems()
    showWebsiteModal.value = true
}

// Métodos de cierre
const closeDefectiveModal = () => {
    showDefectiveModal.value = false
    defectiveForm.reset()
}

const closeDamagedModal = () => {
    showDamagedModal.value = false
    damagedForm.reset()
}

const closeReturnModal = () => {
    showReturnModal.value = false
    returnForm.reset()
}

const closeSaleModal = () => {
    showSaleModal.value = false
    saleForm.reset()
}

const closeInternetSaleModal = () => {
    showInternetSaleModal.value = false
    internetSaleForm.reset()
}

const closeMercadoLibreModal = () => {
    showMercadoLibreModal.value = false
    mercadoLibreForm.reset()
}

const closeWebsiteModal = () => {
    showWebsiteModal.value = false
    websiteForm.reset()
}

// Métodos de envío
const submitDefectiveForm = () => {
    defectiveForm.post('/production/mark-defective', {
        onSuccess: () => {
            closeDefectiveModal()
            emit('success', 'Item marcado como defectuoso exitosamente')
        }
    })
}

const submitDamagedForm = () => {
    damagedForm.post('/production/mark-damaged', {
        onSuccess: () => {
            closeDamagedModal()
            emit('success', 'Item marcado como dañado exitosamente')
        }
    })
}

const submitReturnForm = () => {
    returnForm.post('/production/register-return', {
        onSuccess: () => {
            closeReturnModal()
            emit('success', 'Devolución registrada exitosamente')
        }
    })
}

const submitSaleForm = () => {
    saleForm.post('/production/register-sale', {
        onSuccess: () => {
            closeSaleModal()
            emit('success', 'Venta registrada exitosamente')
        }
    })
}

const submitInternetSaleForm = () => {
    internetSaleForm.post('/production/register-internet-sale', {
        onSuccess: () => {
            closeInternetSaleModal()
            emit('success', 'Venta por internet registrada exitosamente')
        }
    })
}

const submitMercadoLibreForm = () => {
    mercadoLibreForm.post('/production/register-mercadolibre-sale', {
        onSuccess: () => {
            closeMercadoLibreModal()
            emit('success', 'Venta de MercadoLibre registrada exitosamente')
        }
    })
}

const submitWebsiteForm = () => {
    websiteForm.post('/production/register-website-sale', {
        onSuccess: () => {
            closeWebsiteModal()
            emit('success', 'Venta por página web registrada exitosamente')
        }
    })
}

// Exponer métodos
defineExpose({
    openDefectiveModal,
    openDamagedModal,
    openReturnModal,
    openSaleModal,
    openInternetSaleModal,
    openMercadoLibreModal,
    openWebsiteModal
})
</script>