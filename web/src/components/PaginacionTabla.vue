<script setup lang="ts">
import type { Paginado } from '@/types'

defineProps<{ meta: Paginado<unknown>['meta'] }>()

const emit = defineEmits<{ cambiar: [pagina: number] }>()
</script>

<template>
  <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
    <span v-if="meta.total > 0">
      Mostrando {{ meta.from }}–{{ meta.to }} de {{ meta.total }}
    </span>
    <span v-else>Sin resultados</span>

    <div class="flex gap-2">
      <button
        type="button"
        class="boton-secundario px-3 py-1"
        :disabled="meta.current_page <= 1"
        @click="emit('cambiar', meta.current_page - 1)"
      >
        Anterior
      </button>
      <button
        type="button"
        class="boton-secundario px-3 py-1"
        :disabled="meta.current_page >= meta.last_page"
        @click="emit('cambiar', meta.current_page + 1)"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>
