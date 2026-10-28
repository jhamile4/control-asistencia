<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { reportesApi } from '@/services/recursos'
import { mensajeDeError } from '@/services/api'
import { useErroresValidacion } from '@/composables/useErroresValidacion'
import { fechaCorta, fechaISO, haceDias } from '@/utils/fechas'
import type { ReporteTardanzas } from '@/types'
import AlertaMensaje from '@/components/AlertaMensaje.vue'

const rango = reactive({ desde: haceDias(30), hasta: fechaISO() })
const reporte = ref<ReporteTardanzas | null>(null)
const cargando = ref(false)
const errorGeneral = ref('')
const { error, capturar, limpiar } = useErroresValidacion()

const totales = computed(() => {
  const filas = reporte.value?.por_area ?? []
  const asistencias = filas.reduce((suma, f) => suma + f.asistencias, 0)
  const tardanzas = filas.reduce((suma, f) => suma + f.tardanzas, 0)
  return {
    asistencias,
    tardanzas,
    porcentaje: asistencias > 0 ? Math.round((tardanzas * 1000) / asistencias) / 10 : 0,
  }
})

async function generar() {
  limpiar()
  errorGeneral.value = ''
  cargando.value = true
  try {
    reporte.value = await reportesApi.tardanzas(rango.desde, rango.hasta)
  } catch (e) {
    if (!capturar(e)) {
      errorGeneral.value = mensajeDeError(e)
    }
  } finally {
    cargando.value = false
  }
}

onMounted(generar)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Reporte de tardanzas</h1>
      <p class="text-sm text-slate-500">Tolerancia de 10 minutos sobre la hora de entrada de cada empleado</p>
    </div>

    <form class="tarjeta flex flex-wrap items-end gap-4 p-4" @submit.prevent="generar">
      <div>
        <label for="desde" class="etiqueta">Desde</label>
        <input id="desde" v-model="rango.desde" type="date" class="campo" :class="{ 'campo-error': error('desde') }" />
      </div>
      <div>
        <label for="hasta" class="etiqueta">Hasta</label>
        <input id="hasta" v-model="rango.hasta" type="date" class="campo" :class="{ 'campo-error': error('hasta') }" />
      </div>
      <button type="submit" class="boton-primario" :disabled="cargando">
        {{ cargando ? 'Generando...' : 'Generar reporte' }}
      </button>
      <p v-if="error('desde') || error('hasta')" class="w-full text-sm text-red-600">
        {{ error('desde') ?? error('hasta') }}
      </p>
    </form>

    <AlertaMensaje v-if="errorGeneral" tipo="error" :mensaje="errorGeneral" @cerrar="errorGeneral = ''" />

    <template v-if="reporte">
      <p class="text-sm text-slate-600">
        Del {{ fechaCorta(reporte.desde) }} al {{ fechaCorta(reporte.hasta) }}:
        <strong>{{ totales.tardanzas }}</strong> tardanzas en <strong>{{ totales.asistencias }}</strong> asistencias
        ({{ totales.porcentaje }} %).
      </p>

      <div class="grid gap-6 lg:grid-cols-3">
        <section class="tarjeta overflow-hidden lg:col-span-2">
          <h2 class="border-b border-slate-200 px-5 py-4 font-semibold text-slate-900">Por área</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="px-4 py-3">Área</th>
                  <th class="px-4 py-3 text-right">Asistencias</th>
                  <th class="px-4 py-3 text-right">Tardanzas</th>
                  <th class="px-4 py-3 text-right">Minutos</th>
                  <th class="w-40 px-4 py-3">% tardanza</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="fila in reporte.por_area" :key="fila.area_id">
                  <td class="px-4 py-3 font-medium text-slate-800">{{ fila.area }}</td>
                  <td class="px-4 py-3 text-right tabular-nums">{{ fila.asistencias }}</td>
                  <td class="px-4 py-3 text-right tabular-nums">{{ fila.tardanzas }}</td>
                  <td class="px-4 py-3 text-right tabular-nums">{{ fila.minutos_tardanza }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-amber-500" :style="{ width: `${fila.porcentaje_tardanza}%` }" />
                      </div>
                      <span class="w-12 text-right text-xs tabular-nums text-slate-600">{{ fila.porcentaje_tardanza }} %</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="tarjeta">
          <h2 class="border-b border-slate-200 px-5 py-4 font-semibold text-slate-900">Más tardanzas</h2>
          <p v-if="reporte.mas_tardanzas.length === 0" class="px-5 py-8 text-center text-sm text-slate-500">
            Sin tardanzas en este rango.
          </p>
          <ol v-else class="divide-y divide-slate-100">
            <li v-for="(fila, i) in reporte.mas_tardanzas" :key="fila.empleado_id" class="flex gap-3 px-5 py-3 text-sm">
              <span class="font-bold text-slate-400">{{ i + 1 }}</span>
              <div class="flex-1">
                <p class="font-medium text-slate-800">{{ fila.empleado }}</p>
                <p class="text-xs text-slate-500">{{ fila.area }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold tabular-nums">{{ fila.tardanzas }}</p>
                <p class="text-xs text-slate-500">{{ fila.minutos_tardanza }} min</p>
              </div>
            </li>
          </ol>
        </section>
      </div>
    </template>
  </div>
</template>
