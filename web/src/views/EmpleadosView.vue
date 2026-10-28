<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import { areasApi, empleadosApi } from '@/services/recursos'
import { mensajeDeError } from '@/services/api'
import { useSesionStore } from '@/stores/sesion'
import type { Area, Empleado, Paginado } from '@/types'
import AlertaMensaje from '@/components/AlertaMensaje.vue'
import PaginacionTabla from '@/components/PaginacionTabla.vue'

const sesion = useSesionStore()

const areas = ref<Area[]>([])
const resultado = ref<Paginado<Empleado> | null>(null)
const cargando = ref(false)
const aviso = ref<{ tipo: 'error' | 'exito'; mensaje: string } | null>(null)

const filtros = reactive({
  buscar: '',
  area_id: null as number | null,
  activo: null as boolean | null,
  page: 1,
})

async function cargar() {
  cargando.value = true
  try {
    resultado.value = await empleadosApi.listar(filtros)
  } catch (e) {
    aviso.value = { tipo: 'error', mensaje: mensajeDeError(e) }
  } finally {
    cargando.value = false
  }
}

// Espera 300 ms después de la última tecla antes de buscar (debounce)
let temporizador: ReturnType<typeof setTimeout> | undefined
watch(
  () => filtros.buscar,
  () => {
    clearTimeout(temporizador)
    temporizador = setTimeout(() => {
      filtros.page = 1
      cargar()
    }, 300)
  },
)

// Cambiar de área o estado vuelve a la página 1
watch(
  () => [filtros.area_id, filtros.activo],
  () => {
    filtros.page = 1
    cargar()
  },
)

function irAPagina(pagina: number) {
  filtros.page = pagina
  cargar()
}

async function eliminar(empleado: Empleado) {
  if (!confirm(`¿Eliminar a ${empleado.nombre_completo}?`)) return
  try {
    await empleadosApi.eliminar(empleado.id)
    aviso.value = { tipo: 'exito', mensaje: `${empleado.nombre_completo} fue eliminado.` }
    cargar()
  } catch (e) {
    aviso.value = { tipo: 'error', mensaje: mensajeDeError(e) }
  }
}

onMounted(async () => {
  areas.value = await areasApi.listar()
  cargar()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Empleados</h1>
        <p class="text-sm text-slate-500">{{ resultado?.meta.total ?? 0 }} registrados</p>
      </div>
      <RouterLink v-if="sesion.esAdmin" :to="{ name: 'empleado-nuevo' }" class="boton-primario">
        + Nuevo empleado
      </RouterLink>
    </div>

    <AlertaMensaje v-if="aviso" :tipo="aviso.tipo" :mensaje="aviso.mensaje" @cerrar="aviso = null" />

    <div class="grid gap-3 sm:grid-cols-3">
      <input v-model="filtros.buscar" type="search" class="campo" placeholder="Buscar por nombre, apellido o DNI" />
      <select v-model="filtros.area_id" class="campo" aria-label="Filtrar por área">
        <option :value="null">Todas las áreas</option>
        <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
      </select>
      <select v-model="filtros.activo" class="campo" aria-label="Filtrar por estado">
        <option :value="null">Activos e inactivos</option>
        <option :value="true">Solo activos</option>
        <option :value="false">Solo inactivos</option>
      </select>
    </div>

    <div class="tarjeta overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">Empleado</th>
              <th class="px-4 py-3">DNI</th>
              <th class="px-4 py-3">Área</th>
              <th class="px-4 py-3">Entrada</th>
              <th class="px-4 py-3">Estado</th>
              <th v-if="sesion.esAdmin" class="px-4 py-3 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100" :class="{ 'opacity-50': cargando }">
            <tr v-for="empleado in resultado?.data" :key="empleado.id">
              <td class="px-4 py-3">
                <p class="font-medium text-slate-800">{{ empleado.nombre_completo }}</p>
                <p class="text-xs text-slate-500">{{ empleado.email }}</p>
              </td>
              <td class="px-4 py-3 tabular-nums">{{ empleado.dni }}</td>
              <td class="px-4 py-3">{{ empleado.area?.nombre }}</td>
              <td class="px-4 py-3 tabular-nums">{{ empleado.hora_entrada }}</td>
              <td class="px-4 py-3">
                <span
                  class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                  :class="empleado.activo ? 'bg-teal-100 text-teal-800' : 'bg-slate-200 text-slate-600'"
                >
                  {{ empleado.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td v-if="sesion.esAdmin" class="whitespace-nowrap px-4 py-3 text-right">
                <RouterLink
                  :to="{ name: 'empleado-editar', params: { id: empleado.id } }"
                  class="font-semibold text-teal-700 hover:underline"
                >
                  Editar
                </RouterLink>
                <button type="button" class="ml-4 font-semibold text-red-700 hover:underline" @click="eliminar(empleado)">
                  Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="resultado && resultado.data.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay empleados con esos filtros.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <PaginacionTabla v-if="resultado" :meta="resultado.meta" @cambiar="irAPagina" />
    </div>
  </div>
</template>
