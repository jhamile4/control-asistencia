<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { areasApi, empleadosApi } from '@/services/recursos'
import { mensajeDeError } from '@/services/api'
import { useErroresValidacion } from '@/composables/useErroresValidacion'
import type { Area, Empleado, EmpleadoFormulario } from '@/types'
import EmpleadoFormularioComponente from '@/components/EmpleadoFormulario.vue'
import AlertaMensaje from '@/components/AlertaMensaje.vue'

// Si llega "id" es edición; si no, es un empleado nuevo
const props = defineProps<{ id?: number }>()

const router = useRouter()
const { errores, capturar, limpiar } = useErroresValidacion()

const areas = ref<Area[]>([])
const empleado = ref<Empleado | null>(null)
const guardando = ref(false)
const errorGeneral = ref('')

const esEdicion = computed(() => props.id !== undefined)

onMounted(async () => {
  try {
    areas.value = await areasApi.listar()
    if (props.id !== undefined) {
      empleado.value = await empleadosApi.obtener(props.id)
    }
  } catch (e) {
    errorGeneral.value = mensajeDeError(e)
  }
})

async function guardar(datos: EmpleadoFormulario) {
  limpiar()
  errorGeneral.value = ''
  guardando.value = true
  try {
    if (props.id !== undefined) {
      await empleadosApi.actualizar(props.id, datos)
    } else {
      await empleadosApi.crear(datos)
    }
    router.push({ name: 'empleados' })
  } catch (e) {
    if (!capturar(e)) {
      errorGeneral.value = mensajeDeError(e)
    }
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-2xl space-y-6">
    <div>
      <RouterLink :to="{ name: 'empleados' }" class="text-sm font-semibold text-teal-700 hover:underline">
        ← Volver a empleados
      </RouterLink>
      <h1 class="mt-2 text-2xl font-bold text-slate-900">
        {{ esEdicion ? `Editar a ${empleado?.nombre_completo ?? '...'}` : 'Nuevo empleado' }}
      </h1>
    </div>

    <AlertaMensaje v-if="errorGeneral" tipo="error" :mensaje="errorGeneral" @cerrar="errorGeneral = ''" />

    <div class="tarjeta p-6">
      <EmpleadoFormularioComponente
        :areas="areas"
        :empleado="empleado"
        :errores="errores"
        :guardando="guardando"
        @guardar="guardar"
        @cancelar="router.push({ name: 'empleados' })"
      />
    </div>
  </div>
</template>
