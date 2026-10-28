/** Fecha local en formato YYYY-MM-DD (sin pasar por UTC, que en Lima cambia el día por la noche). */
export function fechaISO(fecha: Date = new Date()): string {
  const año = fecha.getFullYear()
  const mes = String(fecha.getMonth() + 1).padStart(2, '0')
  const dia = String(fecha.getDate()).padStart(2, '0')
  return `${año}-${mes}-${dia}`
}

export function haceDias(dias: number): string {
  const fecha = new Date()
  fecha.setDate(fecha.getDate() - dias)
  return fechaISO(fecha)
}

/** "2026-10-28" → "miércoles, 28 de octubre de 2026" */
export function fechaLarga(iso: string): string {
  const [año, mes, dia] = iso.split('-').map(Number)
  return new Date(año!, mes! - 1, dia!).toLocaleDateString('es-PE', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

/** "2026-10-28" → "28/10/2026" */
export function fechaCorta(iso: string): string {
  const [año, mes, dia] = iso.split('-')
  return `${dia}/${mes}/${año}`
}
