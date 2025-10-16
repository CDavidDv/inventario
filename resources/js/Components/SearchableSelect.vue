<template>
  <div class="relative" ref="containerRef">
    <div class="relative">
      <input
        ref="inputRef"
        v-model="searchQuery"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        :class="inputClass"
        @focus="handleFocus"
        @blur="handleBlur"
        @keydown="handleKeydown"
        @input="handleInput"
      />
      
      <!-- Indicador de carga -->
      <div v-if="loading" class="absolute inset-y-0 right-8 flex items-center">
        <LoadingSpinner size="sm" color="gray" />
      </div>
      
      <!-- Icono de dropdown -->
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </div>
    </div>
    
    <!-- Mensaje de error -->
    <div v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </div>
    
    <!-- Dropdown con opciones -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showOptions"
        class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
      >
        <!-- Opción para limpiar selección -->
        <div
          v-if="clearable && selectedOption"
          class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-50 text-gray-500"
          @click="clearSelection"
        >
          <div class="flex items-center">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Limpiar selección
          </div>
        </div>
        
        <!-- Opciones filtradas -->
        <div
          v-for="(option, index) in filteredOptions"
          :key="getOptionKey(option, index)"
          class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50"
          :class="{
            'bg-blue-100': index === highlightedIndex,
            'bg-blue-50 text-blue-900': isSelected(option)
          }"
          @click="selectOption(option)"
        >
          <div class="flex items-center">
            <!-- Icono personalizado de la opción -->
            <div v-if="option[iconKey]" class="flex-shrink-0 mr-3">
              <div 
                class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                :style="{ backgroundColor: option[iconKey] }"
                :class="option[iconTextClass] || 'text-white'"
              >
                {{ getOptionIcon(option) }}
              </div>
            </div>
            
            <div class="flex-1 min-w-0">
              <!-- Texto principal -->
              <div class="font-medium truncate" v-html="highlightMatch(getOptionLabel(option))"></div>
              
              <!-- Texto secundario -->
              <div v-if="option[descriptionKey]" class="text-sm text-gray-500 truncate">
                {{ option[descriptionKey] }}
              </div>
            </div>
          </div>
          
          <!-- Indicador de selección -->
          <div
            v-if="isSelected(option)"
            class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600"
          >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
          </div>
        </div>
        
        <!-- Mensaje cuando no hay opciones -->
        <div
          v-if="filteredOptions.length === 0 && !loading"
          class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-500"
        >
          {{ noOptionsText }}
        </div>
        
        <!-- Indicador de carga en dropdown -->
        <div
          v-if="loading && showOptions"
          class="relative cursor-default select-none py-4 pl-3 pr-9 text-center text-gray-500"
        >
          <LoadingSpinner size="sm" color="gray" text="Cargando..." />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import LoadingSpinner from './LoadingSpinner.vue'

const props = defineProps({
  modelValue: {
    type: [Object, String, Number],
    default: null
  },
  options: {
    type: Array,
    default: () => []
  },
  labelKey: {
    type: String,
    default: 'label'
  },
  valueKey: {
    type: String,
    default: 'value'
  },
  descriptionKey: {
    type: String,
    default: 'description'
  },
  iconKey: {
    type: String,
    default: 'icon'
  },
  iconTextClass: {
    type: String,
    default: 'text-white'
  },
  placeholder: {
    type: String,
    default: 'Buscar...'
  },
  noOptionsText: {
    type: String,
    default: 'No se encontraron opciones'
  },
  searchable: {
    type: Boolean,
    default: true
  },
  clearable: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  },
  minSearchLength: {
    type: Number,
    default: 0
  },
  debounceMs: {
    type: Number,
    default: 300
  }
})

const emit = defineEmits(['update:modelValue', 'search', 'select', 'clear'])

const containerRef = ref(null)
const inputRef = ref(null)
const searchQuery = ref('')
const showOptions = ref(false)
const highlightedIndex = ref(-1)
const debounceTimer = ref(null)

const selectedOption = computed(() => {
  if (!props.modelValue) return null
  
  if (typeof props.modelValue === 'object') {
    return props.modelValue
  }
  
  return props.options.find(option => 
    getOptionValue(option) === props.modelValue
  )
})

const inputClass = computed(() => ({
  'border-red-300 focus:border-red-300 focus:ring-red-200': props.error,
  'opacity-50 cursor-not-allowed': props.disabled
}))

const filteredOptions = computed(() => {
  if (!props.searchable || searchQuery.value.length < props.minSearchLength) {
    return props.options
  }
  
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option => {
    const label = getOptionLabel(option).toLowerCase()
    const description = option[props.descriptionKey]?.toLowerCase() || ''
    return label.includes(query) || description.includes(query)
  })
})

const getOptionLabel = (option) => {
  if (typeof option === 'string') return option
  if (typeof option === 'object') return option[props.labelKey] || ''
  return String(option)
}

const getOptionValue = (option) => {
  if (typeof option === 'string' || typeof option === 'number') return option
  if (typeof option === 'object') return option[props.valueKey] || option
  return option
}

const getOptionKey = (option, index) => {
  const value = getOptionValue(option)
  return value !== undefined ? value : index
}

const getOptionIcon = (option) => {
  const label = getOptionLabel(option)
  return label.charAt(0).toUpperCase()
}

const isSelected = (option) => {
  if (!selectedOption.value) return false
  return getOptionValue(option) === getOptionValue(selectedOption.value)
}

const highlightMatch = (text) => {
  if (!props.searchable || !searchQuery.value) return text
  
  const query = searchQuery.value.toLowerCase()
  const index = text.toLowerCase().indexOf(query)
  
  if (index === -1) return text
  
  return text.slice(0, index) + 
         `<mark class="bg-yellow-200">${text.slice(index, index + query.length)}</mark>` + 
         text.slice(index + query.length)
}

const handleFocus = () => {
  if (props.disabled) return
  showOptions.value = true
  highlightedIndex.value = -1
  
  if (selectedOption.value) {
    searchQuery.value = getOptionLabel(selectedOption.value)
  }
}

const handleBlur = () => {
  // Delay para permitir clicks en opciones
  setTimeout(() => {
    showOptions.value = false
    
    // Restaurar texto si no hay selección válida
    if (!selectedOption.value) {
      searchQuery.value = ''
    } else {
      searchQuery.value = getOptionLabel(selectedOption.value)
    }
  }, 150)
}

const handleInput = () => {
  if (!props.searchable) return
  
  // Limpiar selección si el usuario está escribiendo
  if (selectedOption.value && searchQuery.value !== getOptionLabel(selectedOption.value)) {
    emit('update:modelValue', null)
    emit('clear')
  }
  
  // Debounce para búsqueda
  if (debounceTimer.value) {
    clearTimeout(debounceTimer.value)
  }
  
  debounceTimer.value = setTimeout(() => {
    emit('search', searchQuery.value)
  }, props.debounceMs)
}

const handleKeydown = (event) => {
  if (props.disabled) return
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      if (!showOptions.value) {
        showOptions.value = true
      } else {
        highlightedIndex.value = Math.min(
          highlightedIndex.value + 1,
          filteredOptions.value.length - 1
        )
      }
      break
      
    case 'ArrowUp':
      event.preventDefault()
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
      break
      
    case 'Enter':
      event.preventDefault()
      if (highlightedIndex.value >= 0 && filteredOptions.value[highlightedIndex.value]) {
        selectOption(filteredOptions.value[highlightedIndex.value])
      }
      break
      
    case 'Escape':
      showOptions.value = false
      inputRef.value?.blur()
      break
  }
}

const selectOption = (option) => {
  emit('update:modelValue', getOptionValue(option))
  emit('select', option)
  
  searchQuery.value = getOptionLabel(option)
  showOptions.value = false
  highlightedIndex.value = -1
  
  nextTick(() => {
    inputRef.value?.blur()
  })
}

const clearSelection = () => {
  emit('update:modelValue', null)
  emit('clear')
  
  searchQuery.value = ''
  showOptions.value = false
  highlightedIndex.value = -1
  
  nextTick(() => {
    inputRef.value?.focus()
  })
}

// Inicializar el input con el valor seleccionado
watch(() => props.modelValue, (newValue) => {
  if (newValue && selectedOption.value) {
    searchQuery.value = getOptionLabel(selectedOption.value)
  } else {
    searchQuery.value = ''
  }
}, { immediate: true })

// Click fuera para cerrar
const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    showOptions.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (debounceTimer.value) {
    clearTimeout(debounceTimer.value)
  }
})
</script>

<style scoped>
mark {
  background-color: #fef08a;
  color: inherit;
  padding: 0;
}
</style>
