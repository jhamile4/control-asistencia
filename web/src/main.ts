import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { registrarAlExpirarSesion } from './services/api'
import { useSesionStore } from './stores/sesion'
import { vAutofocus } from './directives/autofocus'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.directive('autofocus', vAutofocus)

// Si la API responde 401 (token vencido), se cierra la sesión y se vuelve al login
registrarAlExpirarSesion(() => {
  useSesionStore().limpiar()
  router.push({ name: 'login' })
})

app.mount('#app')
