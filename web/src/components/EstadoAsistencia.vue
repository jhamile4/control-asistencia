<script setup lang="ts">
import type { Asistencia } from '@/types'

defineProps<{
  asistencia: Asistencia
  procesando: boolean
}>()

const emit = defineEmits<{ salida: [] }>()
</script>

<template>
  <div class="flex items-center gap-3 text-sm">
    <span class="tabular-nums text-slate-600">
      {{ asistencia.hora_entrada }}<template v-if="asistencia.hora_salida"> – {{ asistencia.hora_salida }}</template>
    </span>
    <span
      class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
      :class="asistencia.llego_tarde ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800'"
    >
      {{ asistencia.llego_tarde ? `Tarde ${asistencia.minutos_tardanza} min` : 'Puntual' }}
    </span>
    <button
      v-if="!asistencia.hora_salida"
      type="button"
      class="boton-secundario px-3 py-1.5"
      :disabled="procesando"
      @click="emit('salida')"
    >
      Marcar salida
    </button>
    <span v-else class="text-xs font-medium text-slate-500">Jornada cerrada</span>
  </div>
</template>
