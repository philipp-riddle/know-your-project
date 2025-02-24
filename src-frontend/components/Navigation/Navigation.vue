<template>
    <div class="navigation-sidebar p-3 row">
        <div class="col-sm-12 col-md-4 m-0 p-0 d-flex flex-row gap-2 align-items-center">
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
            <button
                class="nav-link btn p-2 d-flex flex-row gap-3 align-items-center"
                :class="{inactive: !searchStore.isSearching, active: searchStore.isSearching}"
                @click="searchStore.toggleIsSearching"
            >
                <font-awesome-icon :icon="['fa', 'search']" />
                Search and ask
            </button>
        </div>

        <div class="col-sm-12 col-md-4 m-0 p-0 d-flex flex-row justify-content-center align-items-center">
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

        <div class="col-sm-12 col-md-4 m-0 p-0 d-flex flex-row justify-content-end align-items-center gap-2">
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
    import { useSearchStore } from '@/stores/SearchStore.js';
    import { usePageStore } from '@/stores/PageStore.js';
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
    const userStore = useUserStore();

    const isSelected = (navigationItem) => {
        return currentRoute.name?.includes(navigationItem) ?? false;
    };
</script>