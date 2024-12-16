<template>
    <VMenu
        :distance="8"
        :shown="showPopover"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <div class="d-flex flex-row btn btn-primary nav-create-item" v-tooltip="tooltip">
            <span class="white"><font-awesome-icon :icon="['fas', 'pencil']" /></span>
        </div>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="m-4">
                <div class="d-flex flex-column justify-content-center">
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
                                        <small class="text-muted">{{ createMode.description }}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </VMenu>
</template>

<script setup>
    import { defineProps, ref, computed, onMounted } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';

    const props = defineProps({
        project: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: false,
            default: null,
        },
    });
    const createModes = [
        {
            mode: 'note',
            name: 'Note',
            description: 'Only accessible by yourself',
            'icon': 'lock',
        },
        {
            mode: 'page',
            name: 'page',
            description: 'Accessible by everyone in your project',
            'icon': 'file',
        },
    ];
    const currentRoute = useRoute();
    const userStore = useUserStore();
    const currentUser = ref(null)
    const router = useRouter();
    const pageStore = usePageStore();
    const showPopover = ref(false);
    const tooltip = computed(() => {
        return 'Create page - for you, for others?';
    });

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
        let pageObject = {
            name: 'New ' + createMode,
            project: currentUser.value.selectedProject.id,
        }

        // if it's a note it belongs to a specific user
        if (createMode === 'note') {
            pageObject['user'] = currentUser.value.id;
        }

        pageStore.createPage(pageObject).then((page) => {
            pageStore.displayedPages.push(page);
            pageStore.setSelectedPage(page).then(() => {
                router.push({ name: 'Page', params: {id: page.id}});
            });
        });

        return true;
    };
</script>