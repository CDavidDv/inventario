import axios from 'axios';

// Configurar Axios para incluir automáticamente el token CSRF
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configurar la URL base
axios.defaults.baseURL = window.location.origin;

// Variable para rastrear si ya estamos refrescando el token
let isRefreshingToken = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve(token);
        }
    });
    failedQueue = [];
};

// Interceptor para manejar errores
axios.interceptors.response.use(
    response => response,
    async error => {
        const originalRequest = error.config;

        // Si es error 419 (CSRF token mismatch)
        if (error.response?.status === 419 && !originalRequest._retry) {
            if (isRefreshingToken) {
                // Si ya estamos refrescando, añadir a la cola
                return new Promise((resolve, reject) => {
                    failedQueue.push({ resolve, reject });
                }).then(() => {
                    return axios(originalRequest);
                }).catch(err => {
                    return Promise.reject(err);
                });
            }

            originalRequest._retry = true;
            isRefreshingToken = true;

            try {
                // Intentar obtener una nueva página para refrescar el token
                const response = await fetch(window.location.href, {
                    method: 'GET',
                    credentials: 'same-origin'
                });

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newToken = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (newToken) {
                    // Actualizar el token en el meta tag
                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', newToken);

                    // Actualizar el token en axios
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;

                    // Actualizar el token en la petición original
                    originalRequest.headers['X-CSRF-TOKEN'] = newToken;

                    isRefreshingToken = false;
                    processQueue(null, newToken);

                    // Reintentar la petición original
                    return axios(originalRequest);
                } else {
                    throw new Error('No se pudo obtener el nuevo token');
                }
            } catch (refreshError) {
                processQueue(refreshError, null);
                isRefreshingToken = false;

                // Si falla todo, mostrar mensaje y recargar
                console.error('Error al refrescar el token CSRF:', refreshError);

                // Mostrar mensaje al usuario antes de recargar
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Sesión Expirada',
                        text: 'Tu sesión ha expirado. La página se recargará.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    window.location.reload();
                }

                return Promise.reject(refreshError);
            }
        }

        return Promise.reject(error);
    }
);

export default axios;
