<template>
    <ul class="nav nav-pills nav-fill d-flex flex-column p-0 m-0">
        <li
            v-for="page in pageStore.displayedPages"
            :key="page.id"
            class="nav-item d-flex flex-row align-items-center"
        >
            <div
                class="nav-link d-flex flex-row align-items-center gap-2"
                :class="{'active': pageStore.selectedPage?.id == page.id, 'inactive': pageStore.selectedPage?.id != page.id}"
            >
                <font-awesome-icon
                    v-if="page.user"
                    :icon="['fas', 'lock']"
                    v-tooltip="'Note - only you can see this.'"
                />
                <font-awesome-icon
                    v-if="page.task"
                    :icon="['fas', 'list-check']"
                    v-tooltip="'This page belongs to a task.'"
                />
                <router-link
                    class="nav-link p-1"
                    :to="{ name: 'Page', params: { id: page.id } }"
                    :class="{'active': pageStore.selectedPage?.id == page.id, 'inactive': pageStore.selectedPage?.id != page.id}"
                    @click="pageStore.setSelectedPage(page)"
                >
                    {{ page.name }}
                </router-link>
            </div>

            <div class="nav-item-options" v-if="!page.task">
                <div class="d-flex flex-row align-items-center gap-2">
                    <NavigationCreateContentMenu />
                    <DeletionButton label="page" :onConfirm="() => onPageDelete(page)" />
                </div>
            </div>
        </li>
    </ul>
</template>
<script setup>
    import { defineProps, computed, ref, onMounted } from 'vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useDebounceFn } from '@vueuse/core';

    const props = defineProps({
        project: { // provide a project object if you want to only see all pages of a project, not of the current user.
            type: Object,
            required: false,
            default: null,
        },
    });
    const pageStore = usePageStore();
    const userStore = useUserStore();
    const debouncedPageTitleUpdate = useDebounceFn((page) => {
        pageStore.updatePage(page);
    }, 500);

    onMounted(async () => {
        userStore.getCurrentUser().then((user) => {
            const selectedProject = props.project ?? userStore.currentUser?.selectedProject;
            
            if (!selectedProject) {
                console.error('No project in context in PageList.vue component!');
                return;
            }

            pageStore.getPageList(selectedProject?.id).then((pages) => {
                pageStore.displayedPages = pages;
            });
        })
    });

    const onPageTitleUpdate = async (page, event) => {
        page.name = event.target.value;
        await debouncedPageTitleUpdate({id: page.id, name: page.name});
    };

    const onPageDelete = async (page) => {
        await pageStore.deletePage(page.id);
    };

</script>