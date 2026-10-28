<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSesionStore } from '@/stores/sesion'
import { useErroresValidacion } from '@/composables/useErroresValidacion'
import { mensajeDeError } from '@/services/api'
import AlertaMensaje from '@/components/AlertaMensaje.vue'

const sesion = useSesionStore()
const router = useRouter()
const ruta = useRoute()
const { error, capturar, limpiar } = useErroresValidacion()

const email = ref('')
const password = ref('')
const enviando = ref(false)
const errorGeneral = ref('')

async function ingresar() {
  limpiar()
  errorGeneral.value = ''
  enviando.value = true
  try {
    await sesion.login(email.value, password.value)
    const volver = typeof ruta.query.volver === 'string' ? ruta.query.volver : '/'
    router.push(volver)
  } catch (e) {
    if (!capturar(e)) {
      errorGeneral.value = mensajeDeError(e)
    }
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="grid min-h-screen place-items-center px-4">
    <div class="w-full max-w-sm">
      <div class="mb-8 text-center">
        <span class="mx-auto grid size-12 place-items-center rounded-xl bg-teal-700 font-bold text-white">CA</span>
        <h1 class="mt-4 text-2xl font-bold text-slate-900">Control de Asistencia</h1>
        <p class="mt-1 text-sm text-slate-500">Ingresa con tu cuenta de la empresa</p>
      </div>

      <form class="tarjeta space-y-4 p-6" novalidate @submit.prevent="ingresar">
        <AlertaMensaje v-if="errorGeneral" tipo="error" :mensaje="errorGeneral" @cerrar="errorGeneral = ''" />

        <div>
          <label for="email" class="etiqueta">Correo</label>
          <input
            id="email"
            v-model.trim="email"
            v-autofocus
            type="email"
            autocomplete="username"
            class="campo"
            :class="{ 'campo-error': error('email') }"
          />
          <p v-if="error('email')" class="mt-1 text-xs text-red-600">{{ error('email') }}</p>
        </div>

        <div>
          <label for="password" class="etiqueta">Contraseña</label>
          <input
            id="password"
            v-model="password"
            type="password"
            autocomplete="current-password"
            class="campo"
            :class="{ 'campo-error': error('password') }"
          />
          <p v-if="error('password')" class="mt-1 text-xs text-red-600">{{ error('password') }}</p>
        </div>

        <button type="submit" class="boton-primario w-full" :disabled="enviando">
          {{ enviando ? 'Ingresando...' : 'Ingresar' }}
        </button>
      </form>
    </div>
  </div>
</template>
