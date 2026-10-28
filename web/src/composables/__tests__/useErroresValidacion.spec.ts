import { describe, expect, it } from 'vitest'
import { AxiosError, AxiosHeaders } from 'axios'
import { useErroresValidacion } from '../useErroresValidacion'

function errorHttp(status: number, data: unknown) {
  const config = { headers: new AxiosHeaders() }
  return new AxiosError('error', String(status), config, null, {
    status,
    statusText: '',
    headers: {},
    config,
    data,
  })
}

describe('useErroresValidacion', () => {
  it('guarda los errores 422 de Laravel y devuelve el primero de cada campo', () => {
    const { capturar, error } = useErroresValidacion()

    const eraValidacion = capturar(
      errorHttp(422, { message: 'Datos inválidos', errors: { dni: ['El campo DNI debe tener 8 dígitos.', 'Otro'] } }),
    )

    expect(eraValidacion).toBe(true)
    expect(error('dni')).toBe('El campo DNI debe tener 8 dígitos.')
    expect(error('email')).toBeUndefined()
  })

  it('no captura errores que no son de validación', () => {
    const { capturar, errores } = useErroresValidacion()

    expect(capturar(errorHttp(500, { message: 'Error' }))).toBe(false)
    // Un 422 de regla de negocio trae "message" pero no "errors"
    expect(capturar(errorHttp(422, { message: 'Ya marcó su entrada' }))).toBe(false)
    expect(errores.value).toEqual({})
  })

  it('limpiar borra los errores', () => {
    const { capturar, limpiar, error } = useErroresValidacion()
    capturar(errorHttp(422, { errors: { email: ['Correo inválido'] } }))

    limpiar()

    expect(error('email')).toBeUndefined()
  })
})
