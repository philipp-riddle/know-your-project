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
        // default route when navigating to a certain page without a tag context.
        'path': '/page/:id',
        name: 'Page',
        component: () => import('@/views/Page.vue'),
    },
    {
        // different route to allow the page to be opened in a tag context.
        // if the user then refreshes the page, the tag context is not lost and can be restored => Better UX.
        'path': '/page/:tagName/:id',
        name: 'PageTag',
        component: () => import('@/views/Page.vue'),
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