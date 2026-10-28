import { createRouter, createWebHistory } from 'vue-router'
import { useSesionStore } from '@/stores/sesion'
import type { Rol } from '@/types'

declare module 'vue-router' {
  interface RouteMeta {
    publica?: boolean
    rol?: Rol
    titulo?: string
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue'), meta: { publica: true } },
    { path: '/', name: 'inicio', component: () => import('@/views/InicioView.vue'), meta: { titulo: 'Inicio' } },
    {
      path: '/asistencia',
      name: 'asistencia',
      component: () => import('@/views/AsistenciaView.vue'),
      meta: { titulo: 'Marcar asistencia' },
    },
    {
      path: '/empleados',
      name: 'empleados',
      component: () => import('@/views/EmpleadosView.vue'),
      meta: { titulo: 'Empleados' },
    },
    {
      path: '/empleados/nuevo',
      name: 'empleado-nuevo',
      component: () => import('@/views/EmpleadoFormView.vue'),
      meta: { rol: 'admin', titulo: 'Nuevo empleado' },
    },
    {
      path: '/empleados/:id/editar',
      name: 'empleado-editar',
      component: () => import('@/views/EmpleadoFormView.vue'),
      props: (ruta) => ({ id: Number(ruta.params.id) }),
      meta: { rol: 'admin', titulo: 'Editar empleado' },
    },
    {
      path: '/reportes',
      name: 'reportes',
      component: () => import('@/views/ReportesView.vue'),
      meta: { titulo: 'Reportes' },
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

// Guardia: sin sesión → login; sin el rol requerido → inicio
router.beforeEach(async (destino) => {
  const sesion = useSesionStore()

  if (destino.meta.publica) {
    return sesion.estaAutenticado ? { name: 'inicio' } : true
  }

  if (!sesion.estaAutenticado) {
    return { name: 'login', query: { volver: destino.fullPath } }
  }

  try {
    await sesion.cargarUsuario()
  } catch {
    sesion.limpiar()
    return { name: 'login' }
  }

  if (destino.meta.rol && sesion.usuario?.rol !== destino.meta.rol) {
    return { name: 'inicio' }
  }

  return true
})

router.afterEach((destino) => {
  document.title = destino.meta.titulo ? `${destino.meta.titulo} · Control de Asistencia` : 'Control de Asistencia'
})

export default router
