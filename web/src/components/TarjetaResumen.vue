<script setup lang="ts">
withDefaults(
  defineProps<{
    titulo: string
    valor: number | string
    detalle?: string
    tono?: 'neutro' | 'exito' | 'alerta'
  }>(),
  { tono: 'neutro', detalle: undefined },
)
</script>

<template>
  <div class="tarjeta p-5">
    <p class="text-sm font-medium text-slate-500">{{ titulo }}</p>
    <p
      class="mt-2 text-3xl font-bold tabular-nums"
      :class="{
        'text-slate-900': tono === 'neutro',
        'text-teal-700': tono === 'exito',
        'text-amber-600': tono === 'alerta',
      }"
    >
      {{ valor }}
    </p>
    <p v-if="detalle" class="mt-1 text-xs text-slate-500">{{ detalle }}</p>
  </div>
</template>
