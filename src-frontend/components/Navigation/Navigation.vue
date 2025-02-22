<template>
    <div class="navigation-sidebar p-3 d-flex flex-row align-items-center justify-content-between gap-2">
        <div class="flex-grow-1">
            <button
                class="nav-link btn p-2 btn-dark d-flex gap-3 align-items-start"
                :class="{inactive: !searchStore.isSearching, active: searchStore.isSearching}"
                @click="searchStore.toggleIsSearching"
                v-tooltip="'Search and ask'"
            >
                <font-awesome-icon :icon="['fas', 'search']" />
            </button>
        </div>

        <div
            class="d-flex flex-row justify-content-center align-items-center"
            :class="{'flex-grow-1': pageStore.selectedPage != null}"
        >
            <ul class="nav nav-pills p-2 pt-4 pb-0 d-flex flex-row justify-content-center align-items-center gap-1">
                <li class="nav-item">
                    <NavigationProjectDropdown />
                </li>
                <li class="nav-item" v-for="navigationItem in navigationItems">
                    <router-link
                        class="nav-link btn btn-dark d-flex flex-row align-items-center gap-3"
                        :class="{
                            active: isSelected(navigationItem.name) && !searchStore.isSearching,
                            inactive: !isSelected(navigationItem.name) || searchStore.isSearching,
                        }"
                        :to="{ name: navigationItem.name }"
                        v-tooltip="navigationItem.name"
                    >  
                        <font-awesome-icon :icon="['fas', navigationItem.icon]" />
                        <span v-if="isSelected(navigationItem.name) && !searchStore.isSearching" class="nav-item-name"><p class="m-0">{{ navigationItem.name }}</p></span>
                    </router-link>
                </li>
                <li class="nav-item">
                    <NavigationFilterDropdown />
                </li>
                <li class="nav-item">
                    <NavigationCreateContentMenu />
                </li>
            </ul>
        </div>

        <div class="flex-grow-1 d-flex flex-row justify-content-end align-items-center gap-2">
            <!-- inject at global navigation level to align with the main nav; this toolbar for page controls should always be shown at the top -->
            <PageControlNavigation
                v-if="pageStore.selectedPage"
            />
        </div>
    </div>
</template>

<script setup>
    import { useRoute } from 'vue-router';
    import PageControlNavigation from '@/components/Page/PageControl/PageControlNavigation.vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import NavigationFilterDropdown from '@/components/Navigation/NavigationFilterDropdown.vue';
    import NavigationProjectDropdown from '@/components/Navigation/NavigationProjectDropdown.vue';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import { useSearchStore } from '@/stores/SearchStore.js';
    import { usePageStore } from '@/stores/PageStore.js';

    const navigationItems = [
        {
            'name': 'Wiki',
            'icon': 'book',
        },
        {
            'name': 'Tasks',
            'icon': 'list-check',
        },
        {
            'name': 'People',
            'icon': 'user',
        },
    ];
    const currentRoute = useRoute();
    const taskStore = useTaskStore();
    const searchStore = useSearchStore();
    const pageStore = usePageStore();

    const isSelected = (navigationItem) => {
        return currentRoute.name?.includes(navigationItem) ?? false;
    };
</script>