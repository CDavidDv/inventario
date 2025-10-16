import axios from 'axios';

// Configurar Axios para incluir automáticamente el token CSRF
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configurar la URL base
axios.defaults.baseURL = window.location.origin;

// Interceptor para manejar errores
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            // Token CSRF expirado
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

export default axios;
