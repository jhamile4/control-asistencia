import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import EmpleadoFormulario from '../EmpleadoFormulario.vue'
import { vAutofocus } from '@/directives/autofocus'
import type { Empleado } from '@/types'

const areas = [
  { id: 1, nombre: 'Ventas' },
  { id: 2, nombre: 'Tecnología' },
]

function montar(props: Record<string, unknown> = {}) {
  return mount(EmpleadoFormulario, {
    props: { areas, ...props },
    global: { directives: { autofocus: vAutofocus } },
  })
}

describe('EmpleadoFormulario', () => {
  it('emite "guardar" con los datos escritos', async () => {
    const wrapper = montar()

    await wrapper.get('#nombres').setValue('Lucía')
    await wrapper.get('#apellidos').setValue('Quispe Rojas')
    await wrapper.get('#dni').setValue('45678912')
    await wrapper.get('#email').setValue('lucia@empresa.pe')
    await wrapper.get('#area').setValue('2')
    await wrapper.get('form').trigger('submit')

    expect(wrapper.emitted('guardar')?.[0]?.[0]).toEqual({
      area_id: 2,
      dni: '45678912',
      nombres: 'Lucía',
      apellidos: 'Quispe Rojas',
      email: 'lucia@empresa.pe',
      hora_entrada: '09:00',
      activo: true,
    })
  })

  it('carga los datos del empleado al editar', () => {
    const empleado: Empleado = {
      id: 7,
      dni: '12345678',
      nombres: 'Rosa',
      apellidos: 'Huamán',
      nombre_completo: 'Rosa Huamán',
      email: 'rosa@empresa.pe',
      hora_entrada: '08:00',
      activo: false,
      area: { id: 1, nombre: 'Ventas' },
    }

    const wrapper = montar({ empleado })

    expect((wrapper.get('#nombres').element as HTMLInputElement).value).toBe('Rosa')
    expect((wrapper.get('#hora').element as HTMLInputElement).value).toBe('08:00')
    expect((wrapper.get('#area').element as HTMLSelectElement).value).toBe('1')
  })

  it('muestra el error de validación debajo del campo', () => {
    const wrapper = montar({ errores: { dni: ['El campo DNI debe tener 8 dígitos.'] } })

    expect(wrapper.text()).toContain('El campo DNI debe tener 8 dígitos.')
    expect(wrapper.get('#dni').classes()).toContain('campo-error')
  })

  it('emite "cancelar" sin guardar', async () => {
    const wrapper = montar()

    await wrapper.get('button[type="button"]').trigger('click')

    expect(wrapper.emitted('cancelar')).toHaveLength(1)
    expect(wrapper.emitted('guardar')).toBeUndefined()
  })
})
