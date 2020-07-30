import Vue from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import Buefy from 'buefy'
import axios from 'axios'
import VueAxios from 'vue-axios'
import 'buefy/dist/buefy.css'
import 'bootstrap/dist/css/bootstrap.css'
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
 

Vue.config.productionTip = false
Vue.use(VueAxios, axios)
Vue.use(VueSweetalert2)
Vue.use(Buefy, {
  defaultIconComponent: 'vue-fontawesome',
  defaultIconPack: 'fas'
})

new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
