<template>
    <div class="d-flex flex-row align-items-center p-1 gap-2">
        <VDropdown
            v-model:shown="isDropdownVisible"
        >
            <button
                class="btn btn-dark-gray"
                v-tooltip="'Manage page users'"
                :class="{
                    'inactive': page.users.length === 0,
                }"
            >
                <font-awesome-icon :icon="['fas', 'user']" />
            </button>

            <template #popper>
                <div class="p-2" style="min-width: 15rem">
                    <ul class="nav nav-pills nav-fill d-flex flex-column gap-2">
                        <li class="nav-item" v-for="user in availableProjectUsers" :key="user.id">
                            <button
                                class="nav-link btn"
                                :class="{
                                    'active': page.users.some((pageUser) => pageUser.user.id === user.id),
                                    'inactive': !page.users.some((pageUser) => pageUser.user.id === user.id),
                                }"
                                @click="() => handleUserClick(user)"
                            >
                                <UserBadge
                                    :user="user"
                                    :minimal="false"
                                />
                            </button>
                        </li>
                    </ul>
                </div>
            </template>
        </VDropdown>

        <div
            class="d-flex flex-row align-items-center gap-2"
        >
            <UserBadge
                v-if="page.users.length > 0"
                v-for="pageUser in page.users"
                :key="pageUser.id"
                :user="pageUser.user"
                @click="isDropdownVisible = !isDropdownVisible"
            />
            <button
                v-else
                class="btn btn-dark-gray inactive m-0 text-muted"
                @click="isDropdownVisible = !isDropdownVisible"
            >Add users to collaborate</button>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref, watch } from 'vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import PageTagManagerControl from '@/components/Page/PageControl/Tag/PageTagManagerControl.vue';
    import UserBadge from '@/components/User/UserBadge.vue';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const page = ref(props.page);
    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const userStore = useUserStore();

    const isDropdownVisible = ref(false);
    const isLoading = ref(false);

    /**
     * Filter out all User entities from the selected project; sort them by their user ID.
     * This aligns with the dropdown and thus boosts familiarity as it's always the same order.
     */
    const availableProjectUsers = computed(() => {
        return projectStore.selectedProject.projectUsers.map((projectUser) => projectUser.user).sort((a, b) => a.id - b.id);
    });

    watch(() => pageStore.selectedPage.users, (newUsersValue) => {
        page.value.users = newUsersValue; // this makes it really reliable when new users get added
    }, {deep: true});

    const handleUserClick = (user) => {
        if (isLoading.value) {
            return;
        }

        // when any user is clicked in the dropdown we check if the user is already added to the page;
        // if that is the case we remove the user from the page, otherwise we add the user to the page
        const pageUser = page.value.users.find((pageUser) => pageUser.user.id === user.id);

        if (pageUser) {
            pageStore.removeUserFromPage(pageUser);
        } else {
            pageStore.addUserToPage(page.value, user);
        }
    };
</script>