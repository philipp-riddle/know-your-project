<template>
    <div class="navigation-sidebar p-md-2 p-lg-3 row" v-if="projectStore.selectedProject">
        <div class="col-sm-12 col-md-3 m-0 p-0 d-flex flex-row gap-sm-1 gap-lg-2 align-items-center">
            <router-link
                v-if="userStore.currentUser.profilePicture != null"
                :to="{ name: 'Settings' }"
                class="nav-link btn settings-btn p-2 d-flex flex-row gap-3 align-items-center"
                :style="{ backgroundImage: 'url(' + userStore.currentUser.profilePicture.publicFilePath + ')' }"
                :class="{
                    inactive: currentRoute.name !== 'Settings',
                    active: currentRoute.name == 'Settings',
                }"
            >
                &nbsp;&nbsp;&nbsp;&nbsp;
            </router-link>
            <router-link
                v-else
                :to="{ name: 'Settings' }"
                class="nav-link btn p-2 d-flex flex-row gap-3 align-items-center"
                :class="{
                    inactive: currentRoute.name !== 'Settings',
                    active: currentRoute.name == 'Settings',
                }"
            >
                <font-awesome-icon :icon="['fas', 'cog']" />
            </router-link>
            <!-- <router-link
                :to="{ name: 'Help' }"
                class="nav-link btn p-2 d-flex flex-row gap-3 align-items-center"
                :class="{
                    inactive: currentRoute.name !== 'Help',
                    active: currentRoute.name == 'Help',
                }"
            >
                <font-awesome-icon :icon="['fas', 'question']" />
            </router-link> -->
            <button
                class="nav-link btn p-2 d-flex flex-row gap-3 align-items-center"
                :class="{inactive: !searchStore.isSearching, active: searchStore.isSearching}"
                @click="searchStore.toggleIsSearching"
            >
                <font-awesome-icon :icon="['fa', 'search']" />
                <p class="m-0 d-none d-xl-flex flex-row align-items-center gap-1">{{ projectStore.selectedProject.name }}</p>
            </button>
        </div>

        <div class="col-sm-12 col-md-6 m-0 p-0 d-flex flex-row justify-content-center align-items-center">
            <ul class="nav nav-pills p-2 d-flex flex-row justify-content-center align-items-center gap-1">
                <!-- regular Navigation items; take user to different view -->
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
                        <font-awesome-icon :icon="['fa', navigationItem.icon]" />
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

        <div class="col-sm-12 col-md-3 m-0 p-0 d-flex flex-row justify-content-end align-items-center gap-2">
            <!-- inject at global navigation level to align with the main nav; this toolbar for page controls should always be shown at the top -->
            <PageControlNavigation
                v-if="pageStore.selectedPage && currentRoute.name.includes('Wiki')"
            />
        </div>
    </div>
</template>

<script setup>
    import { useRoute } from 'vue-router';
    import PageControlNavigation from '@/components/Page/PageControl/PageControlNavigation.vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import NavigationFilterDropdown from '@/components/Navigation/NavigationFilterDropdown.vue';
    import { useSearchStore } from '@/stores/SearchStore.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useUserStore } from '@/stores/UserStore.js';

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
            'name': 'Calendar',
            'icon': 'calendar',
        },
        {
            'name': 'People',
            'icon': 'user',
        },
    ];
    const currentRoute = useRoute();
    const searchStore = useSearchStore();
    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const userStore = useUserStore();

    const isSelected = (navigationItem) => {
        return currentRoute.name?.includes(navigationItem) ?? false;
    };
</script>