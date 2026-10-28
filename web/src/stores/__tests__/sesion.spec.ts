import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useSesionStore } from '../sesion'
import { CLAVE_TOKEN } from '@/services/api'
import { authApi } from '@/services/recursos'

vi.mock('@/services/recursos', () => ({
  authApi: {
    login: vi.fn<typeof authApi.login>(),
    yo: vi.fn<typeof authApi.yo>(),
    logout: vi.fn<typeof authApi.logout>(),
  },
}))

describe('store de sesión', () => {
  beforeEach(() => {
    localStorage.clear()
    vi.clearAllMocks()
    setActivePinia(createPinia())
  })

  it('al iniciar sesión guarda el token y el usuario', async () => {
    vi.mocked(authApi.login).mockResolvedValue({
      token: '1|abc',
      usuario: { id: 1, nombre: 'Admin', email: 'admin@asistencia.test', rol: 'admin' },
    })
    const sesion = useSesionStore()

    await sesion.login('admin@asistencia.test', 'clave')

    expect(sesion.estaAutenticado).toBe(true)
    expect(sesion.esAdmin).toBe(true)
    expect(localStorage.getItem(CLAVE_TOKEN)).toBe('1|abc')
  })

  it('al cerrar sesión borra el token aunque la API falle', async () => {
    localStorage.setItem(CLAVE_TOKEN, '1|abc')
    vi.mocked(authApi.logout).mockRejectedValue(new Error('sin conexión'))
    const sesion = useSesionStore()

    await expect(sesion.logout()).rejects.toThrow('sin conexión')

    expect(sesion.estaAutenticado).toBe(false)
    expect(localStorage.getItem(CLAVE_TOKEN)).toBeNull()
  })
})
