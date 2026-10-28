<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { asistenciasApi, empleadosApi } from '@/services/recursos'
import { mensajeDeError } from '@/services/api'
import type { Asistencia, Empleado } from '@/types'
import AlertaMensaje from '@/components/AlertaMensaje.vue'
import EstadoAsistencia from '@/components/EstadoAsistencia.vue'

const empleados = ref<Empleado[]>([])
const asistencias = ref<Asistencia[]>([])
const busqueda = ref('')
const cargando = ref(true)
const procesando = ref<number | null>(null)
const aviso = ref<{ tipo: 'error' | 'exito'; mensaje: string } | null>(null)

// Reloj en vivo: se limpia el intervalo al salir de la pantalla
const ahora = ref(new Date())
const reloj = setInterval(() => (ahora.value = new Date()), 1000)
onUnmounted(() => clearInterval(reloj))
const horaActual = computed(() =>
  ahora.value.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
)

/** Asistencia de hoy por id de empleado, para saber qué botón mostrar. */
const asistenciaPorEmpleado = computed(() => {
  const mapa = new Map<number, Asistencia>()
  for (const a of asistencias.value) {
    if (a.empleado) mapa.set(a.empleado.id, a)
  }
  return mapa
})

const filtrados = computed(() => {
  const texto = busqueda.value.trim().toLowerCase()
  if (!texto) return empleados.value
  return empleados.value.filter(
    (e) => e.nombre_completo.toLowerCase().includes(texto) || e.dni.startsWith(texto),
  )
})

const marcados = computed(() => asistencias.value.length)

async function cargar() {
  cargando.value = true
  try {
    const [lista, delDia] = await Promise.all([
      empleadosApi.listar({ activo: true, por_pagina: 100 }),
      asistenciasApi.delDia(),
    ])
    empleados.value = lista.data
    asistencias.value = delDia
  } catch (e) {
    aviso.value = { tipo: 'error', mensaje: mensajeDeError(e) }
  } finally {
    cargando.value = false
  }
}

async function marcarEntrada(empleado: Empleado) {
  procesando.value = empleado.id
  aviso.value = null
  try {
    const nueva = await asistenciasApi.marcarEntrada(empleado.id)
    asistencias.value.push(nueva)
    const detalle = nueva.llego_tarde ? ` con ${nueva.minutos_tardanza} min de tardanza` : ''
    aviso.value = { tipo: 'exito', mensaje: `Entrada de ${empleado.nombre_completo} a las ${nueva.hora_entrada}${detalle}.` }
  } catch (e) {
    aviso.value = { tipo: 'error', mensaje: mensajeDeError(e) }
  } finally {
    procesando.value = null
  }
}

async function marcarSalida(empleado: Empleado, asistencia: Asistencia) {
  procesando.value = empleado.id
  aviso.value = null
  try {
    const actualizada = await asistenciasApi.marcarSalida(asistencia.id)
    asistencias.value = asistencias.value.map((a) => (a.id === actualizada.id ? actualizada : a))
    aviso.value = { tipo: 'exito', mensaje: `Salida de ${empleado.nombre_completo} a las ${actualizada.hora_salida}.` }
  } catch (e) {
    aviso.value = { tipo: 'error', mensaje: mensajeDeError(e) }
  } finally {
    procesando.value = null
  }
}

onMounted(cargar)
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Marcar asistencia</h1>
        <p class="text-sm text-slate-500">{{ marcados }} de {{ empleados.length }} empleados marcaron entrada hoy</p>
      </div>
      <p class="text-3xl font-bold tabular-nums text-teal-700" aria-live="off">{{ horaActual }}</p>
    </div>

    <AlertaMensaje v-if="aviso" :tipo="aviso.tipo" :mensaje="aviso.mensaje" @cerrar="aviso = null" />

    <input v-model="busqueda" v-autofocus type="search" class="campo max-w-md" placeholder="Buscar por nombre o DNI" />

    <p v-if="cargando" class="text-sm text-slate-500">Cargando empleados...</p>

    <div v-else class="tarjeta overflow-hidden">
      <p v-if="filtrados.length === 0" class="px-5 py-8 text-center text-sm text-slate-500">
        Ningún empleado coincide con "{{ busqueda }}".
      </p>

      <ul v-else class="divide-y divide-slate-100">
        <li
          v-for="empleado in filtrados"
          :key="empleado.id"
          class="flex flex-wrap items-center justify-between gap-3 px-5 py-3"
        >
          <div class="min-w-0">
            <p class="font-medium text-slate-800">{{ empleado.nombre_completo }}</p>
            <p class="text-xs text-slate-500">
              DNI {{ empleado.dni }} · {{ empleado.area?.nombre }} · Entra a las {{ empleado.hora_entrada }}
            </p>
          </div>

          <!-- Si ya marcó hoy se muestra su estado; si no, el botón de entrada -->
          <EstadoAsistencia
            v-if="asistenciaPorEmpleado.has(empleado.id)"
            :asistencia="asistenciaPorEmpleado.get(empleado.id)!"
            :procesando="procesando === empleado.id"
            @salida="marcarSalida(empleado, asistenciaPorEmpleado.get(empleado.id)!)"
          />

          <button
            v-else
            type="button"
            class="boton-primario px-3 py-1.5"
            :disabled="procesando === empleado.id"
            @click="marcarEntrada(empleado)"
          >
            Marcar entrada
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>
