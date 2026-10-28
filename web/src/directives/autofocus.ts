import type { Directive } from 'vue'

/** Directiva personalizada: <input v-autofocus> pone el cursor en el campo al mostrarse. */
export const vAutofocus: Directive<HTMLElement> = {
  mounted(el) {
    el.focus()
  },
}
