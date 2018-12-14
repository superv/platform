import Vue from 'vue'
import SuperVJS from 'superv-js'

const configElement = document.getElementById('config')
const config = JSON.parse(configElement.innerHTML)

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
  mixins: [require('superv-js').LayoutMixin]
})