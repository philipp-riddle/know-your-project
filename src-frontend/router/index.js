import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('@/views/Home.vue'),
    },
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
        'path': '/wiki',
        name: 'Wiki',
        component: () => import('@/views/Wiki.vue'),
        children: [
            {
                // default route when navigating to a certain page without a tag context.
                'path': 'page/:id',
                name: 'WikiPage',
                component: () => import('@/views/Page.vue'),
            },
            {
                // different route to allow the page to be opened in a tag context.
                // if the user then refreshes the page, the tag context is not lost and can be restored => Better UX.
                'path': 'page/:tagName/:id',
                name: 'WikiPageTag',
                component: () => import('@/views/Page.vue'),
            },
        ],
    },
    {
        'path': '/users',
        name: 'People',
        component: () => import('@/views/People.vue'),
    },
    {
        'path': '/calendar',
        name: 'Calendar',
        component: () => import('@/views/Calendar.vue'),
        children: [
            {
                // route for letting users open pages in the calendar in a modal.
                'path': 'page/:id',
                name: 'CalendarPage',
                component: () => import('@/views/TaskDetailModal.vue'),
            },
        ],
    },
    {
        'path': '/settings',
        name: 'Settings',
        component: () => import('@/views/Settings.vue'),
    },
    {
        'path': '/setup',
        name: 'Setup',
        component: () => import('@/views/Setup.vue'),
    },
    // {
    //     path: '/feedback',
    //     name: 'Feedback',
    //     component: () => import('@/views/Feedback.vue'),
    // },
    // {
    //     path: '/help',
    //     name: 'Help',
    //     component: () => import('@/views/Help.vue'),
    // },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});
export default router