import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
    {
        path: '/tasks',
        name: 'Tasks',
        component: () => import('@/views/TaskOverview.vue'),
        children: [
            {
                path: ':id',
                name: 'TasksDetail',
                component: () => import('@/views/TaskDetailModal.vue'),
            }
        ],
    },
    {
        'path': '/page/:id',
        name: 'Page',
        component: () => import('@/views/Page.vue'),
        // children: [
        //     {
        //         path: '/tab/:tabId',
        //         name: 'PageTab',
        //         component: () => import('@/views/PageTab.vue'),
        //     }
        // ],
    },
    {
        'path': '/users',
        name: 'People',
        component: () => import('@/views/People.vue'),
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});
export default router