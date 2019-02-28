import Vue from 'vue'
import SuperVJS from 'superv-js'
import App from './App'

let config
const configElement = document.getElementById('config')
if (configElement) {
   config = JSON.parse(configElement.innerHTML)
}

Vue.config.productionTip = false
Vue.use(SuperVJS, {
    config: {
      name: process.env.VUE_APP_NAME,
      apiUrl: config.apiUrl,
      baseUrl: process.env.BASE_URL
    },
    modules: []
  }
)

new Vue({
  el: '#app',
  name: 'root',
  data() {
    return {
      layouts: { default: App }
    }
  },
  mixins: [require('superv-js').LayoutMixin]
})