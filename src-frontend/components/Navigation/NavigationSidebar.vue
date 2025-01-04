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
                                <span class="">{{ navigationItem.name }}</span>
                            </div>
                            <div v-if="taskStore.getTasks(navigationItem.name)">
                                <small class="text-muted">{{ taskStore.getTasks(navigationItem.name).length }}</small>
                            </div>
                        </div>
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="d-flex flex-column gap-2 page-explorer">
                <NavigationPageExplorer />
            </div>
        </div>

        <NavigationSettingsDropdown />
    </div>
</template>

<script setup>
    import { reactive, computed, ref, onMounted } from 'vue';
    import { useRoute } from 'vue-router';
    import NavigationTop from '@/components/Navigation/NavigationTop.vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import NavigationPageExplorer from '@/components/Navigation/NavigationPageExplorer.vue';
    import NavigationSettingsDropdown from '@/components/Navigation/NavigationSettingsDropdown.vue';
    import { useTaskStore } from '@/stores/TaskStore.js';

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

    const isSelected = (navigationItem) => {
        return currentRoute.name?.includes(navigationItem) ?? false;
    };
</script>

<style scoped lang="sass">
    .navigation-sidebar {
        overflow-x: hidden;
        overflow-y: scroll;
    }

    .btn-dark .nav-link {
        color: white !important;
    }

    .btn-outline-dark {
        .nav-link {
            color: black !important;

            &:hover {
                color: white !important;
            }
        }

        &:hover {
            background-color: #212529;
            color: white;
        }
    }

    .nav-link.inactive {
        background-color: #f8f9fa;
        color: black;
    }
</style>