<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useSesionStore } from '@/stores/sesion'

const ruta = useRoute()
const router = useRouter()
const sesion = useSesionStore()

const enLogin = computed(() => ruta.meta.publica === true)

const enlaces = [
  { nombre: 'inicio', texto: 'Inicio' },
  { nombre: 'asistencia', texto: 'Marcar asistencia' },
  { nombre: 'empleados', texto: 'Empleados' },
  { nombre: 'reportes', texto: 'Reportes' },
]

async function cerrarSesion() {
  await sesion.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <RouterView v-if="enLogin" />

  <div v-else class="min-h-screen">
    <header class="border-b border-slate-200 bg-white">
      <!-- En celular: logo y usuario arriba, menú abajo en una fila; desde sm todo en una línea -->
      <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-8 gap-y-2 px-4 py-3">
        <RouterLink to="/" class="flex items-center gap-2 font-bold text-slate-900">
          <span class="grid size-8 place-items-center rounded-lg bg-teal-700 text-sm text-white">CA</span>
          Control de Asistencia
        </RouterLink>

        <nav class="order-last -mx-1 flex w-full gap-1 overflow-x-auto text-sm whitespace-nowrap [scrollbar-width:none] md:order-none md:w-auto md:flex-1">
          <RouterLink
            v-for="enlace in enlaces"
            :key="enlace.nombre"
            :to="{ name: enlace.nombre }"
            class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100"
            exact-active-class="bg-teal-50 text-teal-800"
          >
            {{ enlace.texto }}
          </RouterLink>
        </nav>

        <div v-if="sesion.usuario" class="flex items-center gap-3 text-sm">
          <div class="hidden text-right leading-tight sm:block">
            <p class="font-medium text-slate-800">{{ sesion.usuario.nombre }}</p>
            <p class="text-xs text-slate-500 capitalize">{{ sesion.usuario.rol }}</p>
          </div>
          <button type="button" class="boton-secundario px-3 py-1.5" @click="cerrarSesion">Salir</button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
      <RouterView />
    </main>
  </div>
</template>
