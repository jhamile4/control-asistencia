/// <reference types="vite/client" />

import type { vAutofocus } from './src/directives/autofocus'

// Permite que TypeScript reconozca la directiva global v-autofocus en las plantillas
declare module 'vue' {
  interface GlobalDirectives {
    vAutofocus: typeof vAutofocus
  }
}
