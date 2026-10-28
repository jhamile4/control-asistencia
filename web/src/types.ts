// Tipos que reflejan las respuestas de la API Laravel (app/Http/Resources)

export type Rol = 'admin' | 'supervisor'

export interface Usuario {
  id: number
  nombre: string
  email: string
  rol: Rol
}

export interface Area {
  id: number
  nombre: string
  empleados_count?: number
}

export interface Empleado {
  id: number
  dni: string
  nombres: string
  apellidos: string
  nombre_completo: string
  email: string
  hora_entrada: string
  activo: boolean
  area?: Area
}

export interface EmpleadoFormulario {
  area_id: number | null
  dni: string
  nombres: string
  apellidos: string
  email: string
  hora_entrada: string
  activo: boolean
}

export interface Asistencia {
  id: number
  fecha: string
  hora_entrada: string
  hora_salida: string | null
  minutos_tardanza: number
  llego_tarde: boolean
  empleado?: Empleado
}

export interface Paginado<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
}

export interface ResumenDelDia {
  fecha: string
  empleados_activos: number
  presentes: number
  tardanzas: number
  sin_marcar: number
}

export interface TardanzaPorArea {
  area_id: number
  area: string
  empleados: number
  asistencias: number
  tardanzas: number
  minutos_tardanza: number
  porcentaje_tardanza: number
}

export interface EmpleadoConTardanzas {
  empleado_id: number
  empleado: string
  area: string
  tardanzas: number
  minutos_tardanza: number
}

export interface ReporteTardanzas {
  desde: string
  hasta: string
  por_area: TardanzaPorArea[]
  mas_tardanzas: EmpleadoConTardanzas[]
}

/** Errores 422 de Laravel: { campo: ['mensaje', ...] } */
export type ErroresValidacion = Record<string, string[]>
