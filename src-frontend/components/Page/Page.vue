<template>
    <div class="row">
        <div class="col-sm-12 offset-md-3 offset-lg-2 col-md-9 col-lg-10">
            <h1 class="m-0"><input class="magic-input" v-model="props.page.name" @keyup="updatePageTitle" v-tooltip="'Edit page title'" /></h1>
        </div>
    </div>

    <div class="d-flex flex-column gap-2 mt-4 mb-3">
        <TaskStatusControl v-if="page.task != null" :task="page.task" />
        <TaskDueDateControl v-if="page.task != null" :task="page.task" />
        <PageTagControl :page="page" />
    </div>

    <div>
        <div class="d-flex flex-row align-items-center gap-4 mb-3">
            <p class="bold m-0">CONTENT</p>
            <hr class="w-100">
        </div>

        <div v-if="pageStore.isLoadingPage">
            <div class="col-sm-12 offset-md-3 col-md-9 offset-xl-2 col-xl-10">
                <p>Loading...</p>
            </div>
        </div>
        <div v-else-if="pageTabStore.selectedTab?.id && pageTabStore.pageTabs[pageTabStore.selectedTab?.id]">
            <PageTab :page="page" :pageTab="pageTabStore.selectedTab" />
        </div>
        <div v-else>
            <div class="alert alert-danger">
                <p>Cannot display selected tab: {{ pageTabStore.selectedTab ?? 'n/a' }}</p>
            </div>
        </div>

        <div class="col-sm-12 col-md-2">
            <div class="mb-3 d-flex flex-row justify-content-end align-items-center">
                <!-- <CreateButton :onClick="() => onPageCreateTab(page)" :tooltip="'Create new tab'" /> -->
            </div>
            <div class="d-flex flex-column gap-2" v-if="page.pageTabs.length > 1">
                <div v-for="pageTab in pageTabStore.pageTabsByPage[page.id]">
                    <div class="d-flex flex-row justify-content-between gap-2">
                        <div class="d-flex flex-row gap-2">
                            <div class="tab-options dropdown">
                                <h5 class="dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
                                    <span>{{ pageTab.emojiIcon ?? '/' }}</span>
                                </h5>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><span class="dropdown-item" href="#" @click.stop="">The emoji selector will implemented at some point!</span></li>
                                </ul>
                            </div>
                            <TextArea
                                placeholder="Tab name..."
                                :onTextSubmit="(text) => onPageTabUpdateName(pageTab, text)"
                                :onTextChange="(text) => onPageTabUpdateName(pageTab, text)"
                                :text="pageTab.name"
                                @click="switchPageTab(pageTab)"
                                class="btn btn-sm m-0"
                                :class="{ 'green-bold': pageTabStore.selectedTab?.id === pageTab.id }"
                            />
                        </div>

                        <div class="tab-options dropdown">
                            <h5 class="dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
                                <font-awesome-icon :icon="['fas', 'ellipsis']" />
                            </h5>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li><span class="dropdown-item" href="#" @click.stop="onPageDeleteTab(pageTab)">Delete</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import PageTab from '@/components/Page/PageTab.vue';
    import CreateButton from '@/components/Util/CreateButton.vue';
    import TextArea from '@/components/Util/TextArea.vue';
    import TaskStatusControl from '@/components/Page/PageControl/TaskStatusControl.vue';
    import TaskDueDateControl from '@/components/Page/PageControl/TaskDueDateControl.vue';
    import PageTagControl from '@/components/Page/PageControl/PageTagControl.vue';
    import { fetchCreatePageTab } from '@/stores/fetch/PageFetcher.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { usePageTabStore } from '@/stores/PageTabStore.js';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { ref, onMounted, watch } from 'vue';
    import { useRoute } from 'vue-router';
    import { useDebounceFn } from '@vueuse/core';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const pageStore = usePageStore();
    const pageTabStore = usePageTabStore();
    const pageSectionStore = usePageSectionStore();
    const debouncedTabUpdate = useDebounceFn(async (pageTab, name) => {
        pageTab.name = name;
        await pageTabStore.updateTab(pageTab);
    }, 500);
    const page = ref(props.page);
    const currentRoute = useRoute();

    onMounted(() => {
        switchPageTab(props.page.pageTabs[0]);
    });

    // this re-fetches the page value on every route change from the store
    watch(() => currentRoute.params.id, async (newPageId) => {
        const newPage = await pageStore.getPage(newPageId);
        page.value = newPage;
        switchPageTab(newPage.pageTabs[0]);
    });

    const switchPageTab = (pageTab) => {
        if (!pageTab) {
            return;
        }

        pageTabStore.setSelectedTab(pageTab);
    };

    const onPageCreateTab = async (page) => {
        const createdTab = await pageTabStore.createTab(page.id, {});

        if (createdTab) {
            switchPageTab(createdTab);
        }
    };

    const onPageTabUpdateName = async (pageTab, name) => {
        await debouncedTabUpdate(pageTab, name);
    };

    const onPageDeleteTab = async (pageTab) => {
        await pageTabStore.deleteTab(props.page.id, pageTab);
    };

    const debouncedPageTitleUpdate = useDebounceFn(async () => {
        await pageStore.updatePage({
            id: pageStore.selectedPage.id,
            name: pageStore.selectedPage.name,
        });
    }, 500);
    const updatePageTitle = async () => {
        await debouncedPageTitleUpdate();
    }
</script>