<script setup lang="ts">
defineProps<{
  tipo: 'error' | 'exito'
  mensaje: string
}>()

const emit = defineEmits<{ cerrar: [] }>()
</script>

<template>
  <div
    role="alert"
    class="flex items-start justify-between gap-4 rounded-lg border px-4 py-3 text-sm"
    :class="
      tipo === 'error'
        ? 'border-red-200 bg-red-50 text-red-800'
        : 'border-teal-200 bg-teal-50 text-teal-800'
    "
  >
    <span>{{ mensaje }}</span>
    <button type="button" class="font-semibold opacity-70 hover:opacity-100" aria-label="Cerrar" @click="emit('cerrar')">
      ✕
    </button>
  </div>
</template>
