<script setup lang="ts">
import { reactive, watch } from 'vue'
import type { Area, Empleado, EmpleadoFormulario, ErroresValidacion } from '@/types'

const props = withDefaults(
  defineProps<{
    areas: Area[]
    empleado?: Empleado | null
    errores?: ErroresValidacion
    guardando?: boolean
  }>(),
  { empleado: null, errores: () => ({}), guardando: false },
)

const emit = defineEmits<{
  guardar: [datos: EmpleadoFormulario]
  cancelar: []
}>()

const formulario = reactive<EmpleadoFormulario>({
  area_id: null,
  dni: '',
  nombres: '',
  apellidos: '',
  email: '',
  hora_entrada: '09:00',
  activo: true,
})

// Al editar, el empleado llega después de cargar: se copian sus datos al formulario
watch(
  () => props.empleado,
  (empleado) => {
    if (!empleado) return
    Object.assign(formulario, {
      area_id: empleado.area?.id ?? null,
      dni: empleado.dni,
      nombres: empleado.nombres,
      apellidos: empleado.apellidos,
      email: empleado.email,
      hora_entrada: empleado.hora_entrada,
      activo: empleado.activo,
    })
  },
  { immediate: true },
)

const error = (campo: keyof EmpleadoFormulario) => props.errores[campo]?.[0]

function enviar() {
  emit('guardar', { ...formulario })
}
</script>

<template>
  <form class="grid gap-5 sm:grid-cols-2" novalidate @submit.prevent="enviar">
    <div>
      <label for="nombres" class="etiqueta">Nombres</label>
      <input id="nombres" v-model="formulario.nombres" v-autofocus class="campo" :class="{ 'campo-error': error('nombres') }" />
      <p v-if="error('nombres')" class="mt-1 text-xs text-red-600">{{ error('nombres') }}</p>
    </div>

    <div>
      <label for="apellidos" class="etiqueta">Apellidos</label>
      <input id="apellidos" v-model="formulario.apellidos" class="campo" :class="{ 'campo-error': error('apellidos') }" />
      <p v-if="error('apellidos')" class="mt-1 text-xs text-red-600">{{ error('apellidos') }}</p>
    </div>

    <div>
      <label for="dni" class="etiqueta">DNI</label>
      <input
        id="dni"
        v-model="formulario.dni"
        inputmode="numeric"
        maxlength="8"
        class="campo"
        :class="{ 'campo-error': error('dni') }"
      />
      <p v-if="error('dni')" class="mt-1 text-xs text-red-600">{{ error('dni') }}</p>
    </div>

    <div>
      <label for="email" class="etiqueta">Correo</label>
      <input id="email" v-model="formulario.email" type="email" class="campo" :class="{ 'campo-error': error('email') }" />
      <p v-if="error('email')" class="mt-1 text-xs text-red-600">{{ error('email') }}</p>
    </div>

    <div>
      <label for="area" class="etiqueta">Área</label>
      <select id="area" v-model="formulario.area_id" class="campo" :class="{ 'campo-error': error('area_id') }">
        <option :value="null" disabled>Selecciona un área</option>
        <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nombre }}</option>
      </select>
      <p v-if="error('area_id')" class="mt-1 text-xs text-red-600">{{ error('area_id') }}</p>
    </div>

    <div>
      <label for="hora" class="etiqueta">Hora de entrada</label>
      <input
        id="hora"
        v-model="formulario.hora_entrada"
        type="time"
        class="campo"
        :class="{ 'campo-error': error('hora_entrada') }"
      />
      <p v-if="error('hora_entrada')" class="mt-1 text-xs text-red-600">{{ error('hora_entrada') }}</p>
      <p v-else class="mt-1 text-xs text-slate-500">Desde 10 minutos después se cuenta como tardanza.</p>
    </div>

    <label class="flex items-center gap-2 text-sm sm:col-span-2">
      <input v-model="formulario.activo" type="checkbox" class="size-4 accent-teal-700" />
      Empleado activo (puede marcar asistencia)
    </label>

    <div class="flex justify-end gap-3 sm:col-span-2">
      <button type="button" class="boton-secundario" @click="emit('cancelar')">Cancelar</button>
      <button type="submit" class="boton-primario" :disabled="guardando">
        {{ guardando ? 'Guardando...' : 'Guardar' }}
      </button>
    </div>
  </form>
</template>
