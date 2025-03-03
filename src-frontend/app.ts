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


const app = createApp(App)
    .use(Router)
    .use(FloatingVue)
    .use(createPinia());

/* add font awesome icon component */
app.component('font-awesome-icon', FontAwesomeIcon)

// add pinia; our state management library
import { createPinia, PiniaVuePlugin } from 'pinia';
app.component('Pinia', createPinia());

// setup the axios interceptor for handling HTTP exceptions (... and displaying them to the user)
import { useExceptionHandler } from '@/composables/ExceptionHandler';
useExceptionHandler().setupInterceptor();

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

// in the last step we mount the app to the div with the id 'app'
app.mount('#app');