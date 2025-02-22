<template>
    <VMenu
        :distance="8"
        :shown="showPopover"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <button type="button" class="btn btn-sm btn-primary">
            <span class="white"><font-awesome-icon :icon="['fas', 'plus']" /></span>
        </button>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="p-2 d-flex flex-column justify-content-center">
                <ul class="nav nav-pills nav-fill d-flex flex-column gap-1">
                    <li class="nav-item" v-for="createMode in createModes">
                        <a
                            class="nav-link inactive d-flex flex-row align-items-center gap-3"
                            href="#/notes"
                            :to="{ name: createMode.name }"
                            @click.stop="onCreateClick($event, createMode.mode)"
                        >
                            <font-awesome-icon :icon="['fas', createMode.icon]" />   
                            <div class="d-flex flex-column">
                                Create {{ createMode.mode }}
                                <div v-if="createMode.description">
                                    <p class="text-muted m-0">{{ createMode.description }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </template>
    </VMenu>
</template>

<script setup>
    import {  ref, onMounted } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useTaskStore } from '@/stores/TaskStore.js';

    const props = defineProps({
        page: {
            type: Object,
            required: false,
            default: null,
        },
    });
    const createModes = [
        {
            mode: 'page',
            name: 'page',
            description: 'Write documentation',
            'icon': 'file',
        },
        {
            mode: 'task',
            name: 'list-check',
            description: 'Track progress in the Kanban board',
            'icon': 'list-check',
        },
    ];
    const currentRoute = useRoute();
    const pageStore = usePageStore();
    const userStore = useUserStore();
    const taskStore = useTaskStore();
    const currentUser = ref(null)
    const router = useRouter();
    const showPopover = ref(false);

    onMounted(() => {
        userStore.getCurrentUser().then((user) => {
            currentUser.value = user;
        });
    });

    const isSelected = (navigationItem) => {
        return currentRoute.name?.includes(navigationItem) ?? false;
    };

    const onCreateClick = (event, createMode) => {
        event.preventDefault();

        if (createMode === 'task') {
            taskStore.createTask('Discover', 'New task').then((task) => {
                router.push({ name: 'TasksDetail', params: {id: task.id}});
            });

            return;
        }

        let pageObject = {
            name: 'New ' + createMode,
            project: currentUser.value.selectedProject.id,
        }

        // if it's a note it belongs to a specific user
        if (createMode === 'note') {
            pageObject['user'] = currentUser.value.id;
        }

        pageStore.createPage(pageObject).then((page) => {
            pageStore.setSelectedPage(page).then(() => {
                router.push({ name: 'WikiPage', params: {id: page.id}});
            });
        });

        return;
    };
</script>