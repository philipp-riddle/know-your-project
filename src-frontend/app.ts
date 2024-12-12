/** styles */
import './styles/app.scss';
import 'bootstrap';
import 'floating-vue/dist/style.css'

import { createApp } from 'vue'
import App from '@/App.vue';
/* import the fontawesome core */
import FloatingVue from 'floating-vue'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';

/* import specific icons */
/* & add icons to the library */
import { faCode, faTrash, faCheck, faChevronUp, faChevronDown, faCog, faPlus } from '@fortawesome/free-solid-svg-icons';
import { fas } from '@fortawesome/free-solid-svg-icons';
library.add(fas);
library.add(faCode, faTrash, faCheck, faChevronUp, faChevronDown, faCog, faPlus);

import Router from '@/router';

import { createPinia, PiniaVuePlugin } from 'pinia';

const app = createApp(App)
    .use(Router)
    .use(FloatingVue)
    .use(createPinia());

/* add font awesome icon component */
app.component('font-awesome-icon', FontAwesomeIcon)
app.component('Pinia', createPinia());

app.mount('#app');