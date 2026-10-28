<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { asistenciasApi, reportesApi } from '@/services/recursos'
import { mensajeDeError } from '@/services/api'
import { fechaISO, fechaLarga } from '@/utils/fechas'
import type { Asistencia, ResumenDelDia } from '@/types'
import TarjetaResumen from '@/components/TarjetaResumen.vue'
import AlertaMensaje from '@/components/AlertaMensaje.vue'

const resumen = ref<ResumenDelDia | null>(null)
const asistencias = ref<Asistencia[]>([])
const cargando = ref(true)
const errorCarga = ref('')

const ultimas = computed(() => [...asistencias.value].reverse().slice(0, 8))
const porcentajePresentes = computed(() => {
  if (!resumen.value || resumen.value.empleados_activos === 0) return 0
  return Math.round((resumen.value.presentes * 100) / resumen.value.empleados_activos)
})

onMounted(async () => {
  try {
    // Las dos peticiones van en paralelo
    const [datosResumen, datosAsistencias] = await Promise.all([
      reportesApi.resumenDelDia(),
      asistenciasApi.delDia(),
    ])
    resumen.value = datosResumen
    asistencias.value = datosAsistencias
  } catch (e) {
    errorCarga.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Resumen del día</h1>
      <p class="text-sm text-slate-500 first-letter:uppercase">{{ fechaLarga(fechaISO()) }}</p>
    </div>

    <AlertaMensaje v-if="errorCarga" tipo="error" :mensaje="errorCarga" @cerrar="errorCarga = ''" />

    <p v-if="cargando" class="text-sm text-slate-500">Cargando...</p>

    <template v-else-if="resumen">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <TarjetaResumen titulo="Empleados activos" :valor="resumen.empleados_activos" />
        <TarjetaResumen
          titulo="Presentes"
          :valor="resumen.presentes"
          :detalle="`${porcentajePresentes} % del personal`"
          tono="exito"
        />
        <TarjetaResumen titulo="Tardanzas" :valor="resumen.tardanzas" tono="alerta" />
        <TarjetaResumen titulo="Sin marcar" :valor="resumen.sin_marcar" detalle="Aún no registran entrada" />
      </div>

      <section class="tarjeta">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <h2 class="font-semibold text-slate-900">Últimas entradas</h2>
          <RouterLink :to="{ name: 'asistencia' }" class="text-sm font-semibold text-teal-700 hover:underline">
            Marcar asistencia →
          </RouterLink>
        </div>

        <p v-if="ultimas.length === 0" class="px-5 py-8 text-center text-sm text-slate-500">
          Nadie ha marcado entrada hoy.
        </p>

        <ul v-else class="divide-y divide-slate-100">
          <li v-for="a in ultimas" :key="a.id" class="flex items-center justify-between gap-4 px-5 py-3 text-sm">
            <div>
              <p class="font-medium text-slate-800">{{ a.empleado?.nombre_completo }}</p>
              <p class="text-xs text-slate-500">{{ a.empleado?.area?.nombre }}</p>
            </div>
            <div class="text-right">
              <p class="font-semibold tabular-nums">{{ a.hora_entrada }}</p>
              <p v-if="a.llego_tarde" class="text-xs font-medium text-amber-600">{{ a.minutos_tardanza }} min tarde</p>
              <p v-else class="text-xs text-teal-700">Puntual</p>
            </div>
          </li>
        </ul>
      </section>
    </template>
  </div>
</template>
