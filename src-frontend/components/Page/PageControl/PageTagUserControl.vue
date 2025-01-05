<template>
    <VDropdown
        :placement="'right'"
        :shown="showPageTagUserControl"
        :triggers="[]"
    >
        <button
            class="btn btn-sm"
            v-tooltip="'Click to add users to this tag'"
            @click.stop="showPageTagUserControl = !showPageTagUserControl"
            v-if="tagPage.users.length == 0"
        >
            <font-awesome-icon :icon="['fas', 'user-plus']" />
        </button>
        <div
            v-else
            @click.stop="showPageTagUserControl = !showPageTagUserControl"
            v-tooltip="'Click to manage users in this tag'"
            class="d-flex flex-row gap-2"
        >
            <span
                class="btn btn-sm"
                :class="{
                    'btn-dark': tagPageUser.projectUser.user.id == userStore.currentUser.id,
                    'text-muted': tagPageUser.projectUser.user.id != userStore.currentUser.id
                }"
                v-for="tagPageUser in tagPage.users"
                :key="tagPageUser.id"
            >
                {{ tagPageUser.projectUser.user.email }}
            </span>
        </div>

        <template #popper>
            <div class="p-2 d-flex flex-column gap-2">
                <input
                    type="text"
                    class="form-control"
                    placeholder="Search"
                    tabIndex="1"
                    ref="projectUserInput"
                    style="z-index: 1000 !important;"
                    @change="handleProjectUserInputChange"
                    @keyup="handleProjectUserInputChange"
                >

                <div class="d-flex flex-column justify-content-center">
                    <div v-if="null == availableProjectUsers">
                        <p>Loading... (no available tags)</p>
                    </div>
                    <div v-else-if="availableProjectUsers.length === 0 && pageStore.selectedPage.tags.length === 0">
                        <p>No users found. <span class="bold">Create one</span> to start.</p>
                    </div>
                    <ul v-else class="nav nav-pills nav-fill d-flex flex-column gap-1">
                        <li v-if="pageStore.selectedPage" class="nav-item" v-for="tagPageUser in tagPageUsers" v-tooltip="'Click to remove ' + tagPageUser.projectUser.user.email + ' from this tag and task'">
                            <button
                                class="nav-link active d-flex flex-column gap-2"
                                @click="() => handleTagPageProjectUserDelete(tagPageUser)"
                            >
                                {{ tagPageUser.projectUser.user.email }}

                                <div>
                                    <div v-for="projectUserTag in tagPageUser.projectUser.tags">
                                        <span class="btn btn-sm white" :style="{'border-color': projectUserTag.tag.color}">{{ projectUserTag.tag.name }}</span>
                                    </div>
                                </div>
                            </button>
                        </li>

                        <li class="nav-item" v-for="projectUser in availableProjectUsers" :key="projectUser.id" v-tooltip="'Click to add this user to this tag and task'">
                            <button
                                class="nav-link inactive d-flex flex-column gap-2"
                                @click="() => handleTagPageProjectUserAdd(projectUser)"
                            >
                                <span>{{ projectUser.user.email }}</span>

                                <div>
                                    <div v-for="projectUserTag in projectUser.tags">
                                        <span class="btn btn-sm" :style="{'border-color': projectUserTag.tag.color}">{{ projectUserTag.tag.name }}</span>
                                    </div>
                                </div>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { defineProps, ref, watch, computed, onMounted } from 'vue';
    import { fetchCreateTagPageProjectUserFromTagId, fetchDeleteTagPageProjectUser } from '@/fetch/TagFetcher.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const props = defineProps({
        tagPage: {
            type: Object,
            required: true,
        },
    });
    const userStore = useUserStore();
    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const showPageTagUserControl = ref(false);
    const availableProjectUsers = ref(null);
    const projectUserInput = ref(null);

    // this makes sure to reload the available tags when the component is mounted
    onMounted(async () => {
        reloadAvailableProjectUsers();
    });

    const tagPageUsers = computed(() => {
        const tagPage = pageStore.selectedPage.tags.find((tagPage) => tagPage.tag.id === props.tagPage.tag.id);

        return tagPage?.users ?? [];
    });

    const reloadAvailableProjectUsers = () => {
        projectStore.getSelectedProject().then((selectedProject) => {
            availableProjectUsers.value = selectedProject.projectUsers;

            availableProjectUsers.value = availableProjectUsers.value.filter((projectUser) => {
                if (tagPageUsers.value.find((tagPageUser) => tagPageUser.projectUser.id === projectUser.id)) {
                    return false; // if it's already in the tag page, don't show it
                }

                if (projectUserInput.value?.value) {
                    return projectUser.user.email.includes(projectUserInput.value.value);
                }

                return true;
            });
        });
    };

    const handleTagPageProjectUserAdd = (projectUser) => {
        fetchCreateTagPageProjectUserFromTagId(projectUser.id, props.tagPage.id).then((createdTagPageProjectUser) => {
            // find index of tag page in page store
            const tagPageIndex = pageStore.selectedPage.tags.findIndex((tagPage) => tagPage.tag.id === props.tagPage.tag.id);

            if (tagPageIndex === -1) {
                console.error('Tag page not found in page store, cannot add user to tag page', props.tagPage.tag.id);
                return;
            }

            const tagPage = pageStore.selectedPage.tags[tagPageIndex];
            tagPage.users.push(createdTagPageProjectUser);

            pageStore.selectedPage.tags[tagPageIndex] = tagPage;

            reloadAvailableProjectUsers();
        });
    };

    const handleTagPageProjectUserDelete = (tagPageProjectUser) => {
        fetchDeleteTagPageProjectUser(tagPageProjectUser.id).then((createdTagPageProjectUser) => {
            // find index of tag page in page store
            const tagPageIndex = pageStore.selectedPage.tags.findIndex((tagPage) => tagPage.tag.id === props.tagPage.tag.id);

            if (tagPageIndex === -1) {
                console.error('Tag page not found in page store, cannot delete user from tag page', props.tagPage.tag.id);
                return;
            }

            const tagPage = pageStore.selectedPage.tags[tagPageIndex];
            tagPage.users = tagPage.users.filter((tagPageUser) => tagPageUser.id !== tagPageProjectUser.id);

            pageStore.selectedPage.tags[tagPageIndex] = tagPage;

            reloadAvailableProjectUsers();
        });
    };

    const handleProjectUserInputChange = () => {
        reloadAvailableProjectUsers();
    };
</script>

<style scoped>
    .tags-container {
        cursor: pointer;
    }
</style>