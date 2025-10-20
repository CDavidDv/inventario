<script setup>
import { ref, computed, watch, onMounted, nextTick, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';
import {
    ChevronLeft,
    ChevronRight,
    User,
    LogOut,
    BarChart3,
    Package,
    Boxes,
    Briefcase,
    Wrench,
    Eye,
    X,
    Menu,
    ArrowUp,
    CarIcon,
    Car,
    PackageOpen,
    History,
    Shield,
    ShieldCheck
} from 'lucide-vue-next';
import Gear from '@/Icons/Gear.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const sidebarCollapsed = ref(false);
const headerVisible = ref(true);
const lastScrollTop = ref(0);
const scrollTimeout = ref(null);
const inactivityTimeout = ref(null);
const isMobile = ref(false);

// Función para verificar si es móvil
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

// Función para cargar el estado del sidebar desde localStorage
const loadSidebarState = () => {
    try {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState !== null) {
            const parsedState = JSON.parse(savedState);
            sidebarCollapsed.value = parsedState;
        }
    } catch (error) {
        console.error('Error loading sidebar state from localStorage:', error);
        sidebarCollapsed.value = false;
    }
};

// Función para guardar el estado del sidebar en localStorage
const saveSidebarState = (state) => {
    try {
        localStorage.setItem('sidebarCollapsed', JSON.stringify(state));
    } catch (error) {
        console.error('Error saving sidebar state to localStorage:', error);
    }
};

// Función para manejar el scroll del header móvil
const handleScroll = () => {
    const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Determinar dirección del scroll
    if (currentScrollTop > lastScrollTop.value && currentScrollTop > 100) {
        // Scroll hacia abajo - ocultar header
        headerVisible.value = false;
    } else if (currentScrollTop < lastScrollTop.value) {
        // Scroll hacia arriba - mostrar header
        headerVisible.value = true;
    }
    
    lastScrollTop.value = currentScrollTop;
    
    // Limpiar timeout anterior
    if (scrollTimeout.value) {
        clearTimeout(scrollTimeout.value);
    }
    
    // Ocultar header después de 3 segundos de inactividad
    scrollTimeout.value = setTimeout(() => {
        if (currentScrollTop > 100) {
            headerVisible.value = false;
        }
    }, 3000);
    
    // Resetear timeout de inactividad
    resetInactivityTimeout();
};

// Función para resetear el timeout de inactividad
const resetInactivityTimeout = () => {
    if (inactivityTimeout.value) {
        clearTimeout(inactivityTimeout.value);
    }
    
    // Ocultar header después de 5 segundos de inactividad
    inactivityTimeout.value = setTimeout(() => {
        if (lastScrollTop.value > 100) {
            headerVisible.value = false;
        }
    }, 5000);
};

// Función para mostrar el header (usado en hover y touch)
const showHeader = () => {
    headerVisible.value = true;
    resetInactivityTimeout();
};

// Función para manejar el resize de la ventana
const handleResize = () => {
    checkMobile(); // Actualizar estado de móvil
    
    if (window.innerWidth >= 768) {
        // En desktop, mostrar header y limpiar event listeners
        headerVisible.value = true;
        window.removeEventListener('scroll', handleScroll);
        document.removeEventListener('touchstart', showHeader);
        document.removeEventListener('mousemove', showHeader);
        
        if (scrollTimeout.value) {
            clearTimeout(scrollTimeout.value);
        }
        if (inactivityTimeout.value) {
            clearTimeout(inactivityTimeout.value);
        }
    } else {
        // En móvil, agregar event listeners
        window.addEventListener('scroll', handleScroll, { passive: true });
        document.addEventListener('touchstart', showHeader);
        document.addEventListener('mousemove', showHeader);
        resetInactivityTimeout();
    }
};

// Cargar el estado al montar el componente
onMounted(() => {
    // Limpiar datos problemáticos del localStorage
    try {
        // Limpiar cualquier dato corrupto de Inertia
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.includes('inertia') || key.includes('scroll')) {
                try {
                    const value = localStorage.getItem(key);
                    JSON.parse(value); // Verificar si es JSON válido
                } catch (e) {
                    localStorage.removeItem(key); // Eliminar si está corrupto
                }
            }
        });
    } catch (error) {
        console.warn('Error cleaning localStorage:', error);
    }

    // Cargar el estado inmediatamente
    loadSidebarState();
    
    // También cargar después de un pequeño delay para asegurar que funcione
    setTimeout(() => {
        loadSidebarState();
    }, 100);
    
    // Agregar event listeners para el header móvil
    if (window.innerWidth < 768) {
        window.addEventListener('scroll', handleScroll, { passive: true });
        document.addEventListener('touchstart', showHeader);
        document.addEventListener('mousemove', showHeader);
        
        // Resetear timeout inicial
        resetInactivityTimeout();
    }
    
    // Agregar listener para resize
    window.addEventListener('resize', handleResize);
    checkMobile(); // Inicializar el estado de móvil
});

// Limpiar event listeners al desmontar
onUnmounted(() => {
    if (scrollTimeout.value) {
        clearTimeout(scrollTimeout.value);
    }
    if (inactivityTimeout.value) {
        clearTimeout(inactivityTimeout.value);
    }
    
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('touchstart', showHeader);
    document.removeEventListener('mousemove', showHeader);
    window.removeEventListener('resize', handleResize);
});

// Observar cambios en sidebarCollapsed y guardar en localStorage
watch(sidebarCollapsed, (newValue) => {
    saveSidebarState(newValue);
}, { immediate: false });

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const { props } = usePage()

// Obtener el rol de forma segura
const role = computed(() => {
    try {
        if (props?.auth?.user?.roles && Array.isArray(props.auth.user.roles) && props.auth.user.roles.length > 0) {
            return props.auth.user.roles[0];
        }
        return null;
    } catch (error) {
        console.warn('Error accessing user role:', error);
        return null;
    }
});

// Helper para verificar permisos
const can = (permission) => {
    try {
        if (!props?.auth?.user?.permissions) return false;
        return props.auth.user.permissions.includes(permission);
    } catch (error) {
        console.warn('Error checking permission:', error);
        return false;
    }
};

// Helper para verificar múltiples permisos (al menos uno)
const canAny = (permissions) => {
    return permissions.some(permission => can(permission));
};

</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="flex h-screen  bg-slate-800">

            <!-- Sidebar principal -->
            <nav class="hidden md:block bg-white border-b border-gray-100 no-print">

                <!-- Primary Navigation Menu -->
                <div :class="[
                    'h-full flex flex-col justify-around min-h-screen bg-primary-500 px-4 transition-all duration-300 ease-in-out sidebar-collapsed',
                    sidebarCollapsed ? 'max-w-[80px] min-w-[80px]' : 'max-w-[300px] min-w-[300px]'
                ]" id="sidebar">

                
                    <div class="flex flex-col h-full">
                        <!-- Botón para colapsar/expandir sidebar -->
                        <div class="flex items-center justify-center">
                        <button 
                                @click="toggleSidebar" 
                                class="mt-6 p-3 text-white hover:bg-white/20 rounded-lg transition-all duration-200 border border-white/50 hover:border-white/40 group collapse-button w-full"
                                :title="sidebarCollapsed ? 'Expandir sidebar' : 'Colapsar sidebar'"
                            >
                                <div class="flex items-center justify-center">
                                    <ChevronLeft v-if="!sidebarCollapsed" class="h-5 w-5 transform group-hover:scale-110 transition-transform duration-200" />
                                    <ChevronRight v-else class="h-5 w-5 transform group-hover:scale-110 transition-transform " />
                                </div>
                            </button>
                        </div>  
                        <div class="justify-between mt-4">
                            <div class="pt-2 h gap-2">
                                
                                <div class="flex h-full justify-between flex-col flex-1">

                                    <div class="mt-4 mb-1">
                                        <!-- Información del usuario -->
                                        <div :class="sidebarCollapsed ? '' : 'gap-3 p-3 bg-white/10'"  class="flex h-16  justify-around items-center   rounded-lg">
                                            <div :class="sidebarCollapsed ? 'w-full h-12' : 'w-10 h-full '" class=" bg-white/20 rounded-full flex items-center justify-center">
                                                
                                                <User class="size-5 text-white" />
                                            </div>
                                            <div v-if="!sidebarCollapsed" class="text-white">
                                                <p class="text-sm font-bold">{{ $page.props?.auth?.user?.name || 'Usuario' }}</p>
                                                <p class="text-xs font-medium  capitalize">{{ role?.name || 'Usuario' }}</p>
                                            </div>

                                            <div v-if="!sidebarCollapsed" class="flex items-center justify-center hover:bg-white/30 transition-colors duration-200" :title="'Configuración de ' + ($page.props?.auth?.user?.name || 'Usuario')">  
                                                <NavLink :href="route('profile.show')" :active="route().current('profile.show')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class=" hover:bg-white/20  transition-colors duration-200">
                                                    <Gear class="" />
                                                </NavLink>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <!--Cerrar sesión-->
                                    <div  class="w-full justify-center sm:flex mb-6">
                                        
                                        <form @submit.prevent="logout" v-if="!sidebarCollapsed">
                                            <button type="submit" class="text-white hover:text-gray-700 transition-colors duration-200">
                                                <div class="flex items-center gap-2">
                                                    <LogOut class="h-5 w-5" />
                                                    <span>Cerrar sesión</span>
                                                </div>
                                            </button>
                                        </form>
                                    </div>

                                    
                                    <!-- Enlaces de navegación -->
                                    <NavLink v-if="can('view_dashboard')" :href="route('dashboard')" :active="route().current('dashboard')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <BarChart3 class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed" class="text-white">Dashboard</span>
                                        </div>
                                    </NavLink>

                                    <NavLink v-if="can('view_inventory')" :href="route('inventario.index')" :active="route().current('inventario.*')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <Package class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed">Inventario</span>
                                        </div>
                                    </NavLink>

                                    <NavLink v-if="can('view_supervisor_dashboard')"
                                        :href="route('supervisor.dashboard')"
                                        :active="route().current('supervisor.*')"
                                        :class="sidebarCollapsed ? 'justify-center' : 'justify-start'"
                                        class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <Eye class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed">Supervisor</span>
                                        </div>
                                    </NavLink>

                                    <NavLink v-if="can('view_production')" :href="route('production.index')" :active="route().current('production.*')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <PackageOpen class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed">Producción</span>
                                        </div>
                                    </NavLink>

                                    <NavLink v-if="can('view_suppliers')" :href="route('suppliers.index')" :active="route().current('suppliers.*')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <Car class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed">Distribuidores</span>
                                        </div>
                                    </NavLink>

                                    <!-- Movimientos de Inventario -->
                                    <NavLink v-if="can('view_movements')" :href="route('inventory-movements.index')" :active="route().current('inventory-movements.*')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <History class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed">Movimientos</span>
                                        </div>
                                    </NavLink>

                                    <!-- Sistema de Auditoría -->
                                    <NavLink v-if="can('view_system_logs')" :href="route('system-logs.index')" :active="route().current('system-logs.*')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <Shield class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed">Auditoría</span>
                                        </div>
                                    </NavLink>

                                    <!-- Administración de Permisos
                                    <NavLink v-if="can('manage-permissions')" :href="route('permissions.index')" :active="route().current('permissions.*')" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" class="text-white hover:bg-white/20 rounded-lg transition-colors duration-200">
                                        <div class="flex items-center gap-3">
                                            <div class="text-white size-8" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'">
                                                <ShieldCheck class="size-full text-white" />
                                            </div>
                                            <span v-if="!sidebarCollapsed">Permisos</span>
                                        </div>
                                    </NavLink>
                                    -->
                                </div>
                                                        
                            </div>

                            
                        </div>

                    </div>
                    <!-- Botón de cerrar sesión para sidebar colapsado - en la parte inferior -->
                    <div v-if="sidebarCollapsed" class="pb-6 ">
                        <form @submit.prevent="logout">
                            <button 
                                type="submit" 
                                class="w-full p-3 text-white hover:bg-white/20 rounded-lg transition-all duration-200 hover:scale-105 group border border-white/30"
                                title="Cerrar sesión"
                            >
                                <LogOut class="h-6 w-6 mx-auto group-hover:translate-x-1 transition-transform duration-200" />
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Sidebar mobile overlay -->
            <div v-if="showingNavigationDropdown" class="fixed inset-0 z-40 md:hidden sidebar-overlay" @click="showingNavigationDropdown = false">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
            </div>

            <!-- Sidebar mobile -->
            <div :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-primary-500 transform transition-transform duration-300 ease-in-out md:hidden sidebar-mobile',
                showingNavigationDropdown ? 'translate-x-0' : '-translate-x-full'
            ]" id="sidebar" >
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between p-4 border-b border-white/20">
                        
                        <button 
                            @click="showingNavigationDropdown = false"
                            class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors duration-200"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>
                    
                    <div class="flex-1 ">
                        <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')" class="text-white hover:bg-white/20 rounded-lg">
                            Perfil
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view_dashboard')" :href="route('dashboard')" :active="route().current('dashboard')" class="text-white hover:bg-white/20 rounded-lg">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view_inventory')" :href="route('inventario.index')" :active="route().current('inventario.*')" class="text-white hover:bg-white/20 rounded-lg">
                            Inventario
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view_supervisor_dashboard')" :href="route('supervisor.dashboard')" :active="route().current('supervisor.*')" class="text-white hover:bg-white/20 rounded-lg">
                            Supervisor
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view_production')" :href="route('production.index')" :active="route().current('production.*')" class="text-white hover:bg-white/20 rounded-lg">
                            Producción
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view_suppliers')" :href="route('suppliers.index')" :active="route().current('suppliers.*')" class="text-white hover:bg-white/20 rounded-lg">
                            Distribuidores
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view_movements')" :href="route('inventory-movements.index')" :active="route().current('inventory-movements.*')" class="text-white hover:bg-white/20 rounded-lg">
                            Movimientos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view_system_logs')" :href="route('system-logs.index')" :active="route().current('system-logs.*')" class="text-white hover:bg-white/20 rounded-lg">
                            Auditoría
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('manage_permissions')" :href="route('permissions.index')" :active="route().current('permissions.*')" class="text-white hover:bg-white/20 rounded-lg">
                            Permisos
                        </ResponsiveNavLink>

                    </div>

                    <div class="p-4 border-t border-white/20">
                        <form @submit.prevent="logout">
                            <button type="submit" class="w-full text-left text-white hover:bg-white/20 rounded-lg p-3 transition-colors duration-200">
                                <div class="flex items-center gap-3">
                                    <LogOut class="h-5 w-5" />
                                    <span>Cerrar sesión</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="flex-1 flex flex-col overflow-hidden ">
                <!-- Header móvil -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="transform -translate-y-full opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition-all duration-300 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-full opacity-0"
                >
                    <header v-show="headerVisible" id="sidebar" class="md:hidden bg-primary-500 text-white px-4 py-2 border-b border-white/20 header-mobile fixed top-0 left-0 right-0 z-50">
                        <div class="flex items-center justify-between">
                            <button 
                                @click="showingNavigationDropdown = true"
                                class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors duration-200"
                            >
                                <Menu class="h-6 w-6" />
                            </button>
                            
                            <!-- Centro de notificaciones móvil -->
                            <div class="text-white">
                                <NotificationCenter />
                            </div>
                        </div>
                    </header>
                </Transition>

                <!-- Indicador de scroll hacia arriba cuando header está oculto -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-300 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-show="!headerVisible && lastScrollTop > 100" class="md:hidden fixed top-4 right-4 z-50">
                        <div id="slidebar" class="bg-primary-500/90 backdrop-blur-sm text-white p-2 rounded-full shadow-lg border border-white/20">
                            <ArrowUp class="h-5 w-5 animate-bounce" />
                        </div>
                    </div>
                </Transition>

                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow-2xl border border-b-4 relative">
                    <div class="max-w-7xl mx-auto md:py-6 md:px-14 sm:px-6 ">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <slot name="header" />
                            </div>
                            
                            <!-- Centro de notificaciones desktop -->
                            <div class="hidden md:block ml-4">
                                <NotificationCenter />
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-auto bg-gray-50 main-content" :class="{ 'pt-16': headerVisible && isMobile }">                    
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
<style scoped>


/* Estilos para el navbar responsive */
@media (max-width: 768px) {
    .sidebar-mobile {
        z-index: 9999;
    }
    
    .sidebar-overlay {
        z-index: 9998;
    }
    
    .header-mobile {
        z-index: 9997;
    }
}

/* Asegurar que el contenido principal no se superponga */
.main-content {
    position: relative;
    z-index: 1;
}

/* Estilos para el sidebar móvil */
.sidebar-mobile {
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
}

/* Transiciones suaves */
.transition-transform {
    transition-property: transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Asegurar que los botones sean táctiles en móvil */
@media (max-width: 768px) {
    button, .nav-link {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Estilos para el sidebar colapsado */
.sidebar-collapsed {
    transition: all 300ms ease-in-out;
}

/* Hover effects mejorados para el botón de colapsar */
.collapse-button:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
}

/* Indicador de expandir cuando está colapsado */
.expand-indicator {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.6;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
}

/* Estilos para el header móvil inteligente */
.header-mobile {
    backdrop-filter: blur(10px);
    background: rgb(128 170 64 / 0.95);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
}

/* Transiciones suaves para el header */
.header-mobile {
    transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* Efecto de sombra cuando el header está visible */
.header-mobile:not(.hidden) {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

/* Asegurar que el contenido principal tenga el padding correcto */
.main-content.pt-16 {
    padding-top: 2rem;
}

/* Mejorar la experiencia de scroll en móvil */
@media (max-width: 768px) {
    .main-content {
        scroll-behavior: smooth;
    }
    
    /* Ocultar scrollbar en móvil para mejor UX */
    .main-content::-webkit-scrollbar {
        display: none;
    }
    
    .main-content {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
}

/* En desktop, asegurar que no haya padding-top extra */
@media (min-width: 768px) {
    .main-content {
        padding-top: 0 !important;
    }
}



</style>