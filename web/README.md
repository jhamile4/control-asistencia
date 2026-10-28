# Control de Asistencia: frontend

Aplicación en Vue 3 + TypeScript que consume la API. Las instrucciones están en el [README principal](../README.md).

| Carpeta | Contenido |
|---|---|
| `src/services` | Cliente axios con el token e interceptor de 401; una función por endpoint |
| `src/stores` | Store de sesión con Pinia |
| `src/composables` | `useErroresValidacion`: errores 422 de Laravel por campo |
| `src/directives` | Directiva `v-autofocus` |
| `src/components` | Formulario de empleado, paginación, alertas, tarjetas |
| `src/views` | Una vista por ruta, cargadas bajo demanda |
