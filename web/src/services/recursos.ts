import api from './api'
import type {
  Area,
  Asistencia,
  Empleado,
  EmpleadoFormulario,
  Paginado,
  ReporteTardanzas,
  ResumenDelDia,
  Usuario,
} from '@/types'

// Una función por endpoint: las vistas no conocen URLs ni axios

export const authApi = {
  async login(email: string, password: string) {
    const { data } = await api.post<{ token: string; usuario: Usuario }>('/login', { email, password })
    return data
  },
  async yo() {
    const { data } = await api.get<{ data: Usuario }>('/yo')
    return data.data
  },
  logout: () => api.post('/logout'),
}

export const areasApi = {
  async listar() {
    const { data } = await api.get<{ data: Area[] }>('/areas')
    return data.data
  },
}

export interface FiltrosEmpleados {
  buscar?: string
  area_id?: number | null
  activo?: boolean | null
  page?: number
  por_pagina?: number
}

export const empleadosApi = {
  async listar(filtros: FiltrosEmpleados = {}) {
    // Quita los filtros vacíos y convierte booleanos a 1/0 para Laravel
    const params = Object.fromEntries(
      Object.entries(filtros)
        .filter(([, valor]) => valor !== '' && valor !== null && valor !== undefined)
        .map(([clave, valor]) => [clave, typeof valor === 'boolean' ? Number(valor) : valor]),
    )
    const { data } = await api.get<Paginado<Empleado>>('/empleados', { params })
    return data
  },
  async obtener(id: number) {
    const { data } = await api.get<{ data: Empleado }>(`/empleados/${id}`)
    return data.data
  },
  async crear(empleado: EmpleadoFormulario) {
    const { data } = await api.post<{ data: Empleado }>('/empleados', empleado)
    return data.data
  },
  async actualizar(id: number, empleado: EmpleadoFormulario) {
    const { data } = await api.put<{ data: Empleado }>(`/empleados/${id}`, empleado)
    return data.data
  },
  eliminar: (id: number) => api.delete(`/empleados/${id}`),
}

export const asistenciasApi = {
  async delDia(fecha?: string) {
    const { data } = await api.get<{ data: Asistencia[] }>('/asistencias', { params: { fecha } })
    return data.data
  },
  async marcarEntrada(empleadoId: number) {
    const { data } = await api.post<{ data: Asistencia }>('/asistencias/entrada', { empleado_id: empleadoId })
    return data.data
  },
  async marcarSalida(asistenciaId: number) {
    const { data } = await api.patch<{ data: Asistencia }>(`/asistencias/${asistenciaId}/salida`)
    return data.data
  },
}

export const reportesApi = {
  async tardanzas(desde: string, hasta: string) {
    const { data } = await api.get<ReporteTardanzas>('/reportes/tardanzas', { params: { desde, hasta } })
    return data
  },
  async resumenDelDia() {
    const { data } = await api.get<ResumenDelDia>('/reportes/resumen-del-dia')
    return data
  },
}
