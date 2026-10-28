import { ref } from 'vue'
import { AxiosError } from 'axios'
import type { ErroresValidacion } from '@/types'

/**
 * Guarda los errores 422 de Laravel para mostrarlos debajo de cada campo.
 * Uso: const { errores, error, capturar, limpiar } = useErroresValidacion()
 */
export function useErroresValidacion() {
  const errores = ref<ErroresValidacion>({})

  /** Primer mensaje de un campo, o undefined si no tiene error. */
  function error(campo: string): string | undefined {
    return errores.value[campo]?.[0]
  }

  /** Devuelve true si era un error de validación (y lo guarda); false si es otro error. */
  function capturar(e: unknown): boolean {
    if (e instanceof AxiosError && e.response?.status === 422) {
      const datos = e.response.data as { errors?: ErroresValidacion }
      if (datos.errors) {
        errores.value = datos.errors
        return true
      }
    }
    return false
  }

  function limpiar() {
    errores.value = {}
  }

  return { errores, error, capturar, limpiar }
}
