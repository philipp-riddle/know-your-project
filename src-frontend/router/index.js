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
                // default route when navigating to a page in the wiki.
                'path': 'page/:id',
                name: 'WikiPage',
                component: () => import('@/views/Page.vue'),
            },
        ],
    },
    {
        // route for managing users in the currently selected project.
        'path': '/users',
        name: 'People',
        component: () => import('@/views/People.vue'),
    },
    {
        // route for viewing the calendar of the currently selected project and all associated task and event deadlines.
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