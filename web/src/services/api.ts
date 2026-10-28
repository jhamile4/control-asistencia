import axios, { AxiosError } from 'axios'

export const CLAVE_TOKEN = 'asistencia.token'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? '/api/v1',
  headers: { Accept: 'application/json' },
})

// Agrega el token de Sanctum a cada petición
api.interceptors.request.use((config) => {
  const token = localStorage.getItem(CLAVE_TOKEN)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

/** Lo registra la app al iniciar: qué hacer cuando la API responde 401 (sesión vencida). */
let alExpirarSesion: () => void = () => {}
export function registrarAlExpirarSesion(fn: () => void) {
  alExpirarSesion = fn
}

api.interceptors.response.use(
  (respuesta) => respuesta,
  (error: AxiosError) => {
    const esLogin = error.config?.url?.endsWith('/login')
    if (error.response?.status === 401 && !esLogin) {
      alExpirarSesion()
    }
    return Promise.reject(error)
  },
)

/** Devuelve el mensaje de error de la API en español, o uno genérico. */
export function mensajeDeError(error: unknown): string {
  if (error instanceof AxiosError) {
    const datos = error.response?.data as { message?: string } | undefined
    if (error.response?.status === 403) return 'No tienes permiso para realizar esta acción.'
    if (error.response?.status === 429) return 'Demasiados intentos. Espera un minuto.'
    if ((error.response?.status ?? 0) >= 502) return 'El servidor no está disponible. Intenta en unos minutos.'
    if (datos?.message) return datos.message
    if (!error.response) return 'No hay conexión con el servidor.'
  }
  return 'Ocurrió un error inesperado.'
}

export default api
