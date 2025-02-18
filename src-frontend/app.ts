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

// before mounting the app we setup the stores with data from the window and default data
import { useProjectStore } from '@/stores/ProjectStore';
import { useUserStore } from '@/stores/UserStore';
import { useTagStore } from '@/stores/TagStore';

useProjectStore().setup();
useUserStore().setup();
useTagStore().setup();

// setup the mercure event subscriber and the connection to Mercure
import { useMercureEventSubscriber } from '@/events/MercureEventSubscriber';
useMercureEventSubscriber().setup(window.mercureConfig); // use the config from the window object for the setup

app.mount('#app');