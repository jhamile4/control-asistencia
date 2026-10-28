import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { CLAVE_TOKEN } from '@/services/api'
import { authApi } from '@/services/recursos'
import type { Usuario } from '@/types'

export const useSesionStore = defineStore('sesion', () => {
  const usuario = ref<Usuario | null>(null)
  const token = ref<string | null>(localStorage.getItem(CLAVE_TOKEN))

  const estaAutenticado = computed(() => token.value !== null)
  const esAdmin = computed(() => usuario.value?.rol === 'admin')

  async function login(email: string, password: string) {
    const respuesta = await authApi.login(email, password)
    token.value = respuesta.token
    usuario.value = respuesta.usuario
    localStorage.setItem(CLAVE_TOKEN, respuesta.token)
  }

  /** Al recargar la página hay token pero no usuario: se pide a /yo. */
  async function cargarUsuario() {
    if (token.value && !usuario.value) {
      usuario.value = await authApi.yo()
    }
  }

  function limpiar() {
    token.value = null
    usuario.value = null
    localStorage.removeItem(CLAVE_TOKEN)
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      limpiar()
    }
  }

  return { usuario, token, estaAutenticado, esAdmin, login, cargarUsuario, logout, limpiar }
})
