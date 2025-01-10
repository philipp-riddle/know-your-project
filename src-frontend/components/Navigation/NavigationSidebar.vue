<template>
    <div class="navigation-sidebar h-100 d-flex flex-column gap-5 justify-content-between p-3">
        <div class="d-flex flex-column gap-5">
            <div class="d-flex flex-column gap-2">
                <NavigationTop />

                <ul class="nav nav-pills nav-fill d-flex flex-column gap-1">
                    <li class="nav-item" v-for="navigationItem in navigationItems">
                        <router-link
                            class="nav-link"
                            :class="{ active: isSelected(navigationItem.name), inactive: !isSelected(navigationItem.name) }"
                            :to="{ name: navigationItem.name }"
                        >   
                        <div class="d-flex flex-row gap-3 align-items-center justify-content-between">
                            <div class="d-flex flex-row gap-3 align-items-center">
                                <font-awesome-icon :icon="['fas', navigationItem.icon]" />
                                <span>{{ navigationItem.name }}</span>
                            </div>
                            <div v-if="taskStore.tasks[navigationItem.name]">
                                <small class="text-muted">{{ taskStore.tasks[navigationItem.name].length }}</small>
                            </div>
                        </div>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <div
                            class="nav-link"
                            :class="{active: searchStore.isSearching, inactive: !searchStore.isSearching}"
                            @click="searchStore.toggleIsSearching"
                        >
                            <div class="d-flex flex-row gap-3 align-items-center">
                                <font-awesome-icon :icon="['fas', 'search']" />
                                <span>Search</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="d-flex flex-column gap-2 page-explorer">
                <PageExplorer />
            </div>
        </div>

        <NavigationSettingsDropdown />
    </div>
</template>

<script setup>
    import { useRoute } from 'vue-router';
    import NavigationTop from '@/components/Navigation/NavigationTop.vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import PageExplorer from '@/components/Page/Explorer/PageExplorer.vue';
    import NavigationSettingsDropdown from '@/components/Navigation/NavigationSettingsDropdown.vue';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import { useSearchStore } from '@/stores/SearchStore.js';

    const navigationItems = [
        {
            name: 'Tasks',
            'icon': 'list-check',
        },
        {
            name: 'People',
            'icon': 'user',
        },
    ];
    const currentRoute = useRoute();
    const taskStore = useTaskStore();
    const searchStore = useSearchStore();

    const isSelected = (navigationItem) => {
        return currentRoute.name?.includes(navigationItem) ?? false;
    };
</script>