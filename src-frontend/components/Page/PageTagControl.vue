<template>
    <VDropdown
        :distance="-3"
        :skidding="-5"
        :placement="'bottom-start'"
    >
        <button class="btn btn-sm m-0 p-0 text-muted" v-tooltip="'Click to add tags'">TAGS</button>

        <template #popper>
            <div class="p-2 d-flex flex-column gap-2">
                <div class="d-flex flex-row justify-content-between gap-1">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Search or create..."
                        ref="tagInput"
                        :disabled="isCreatingTag"
                        @change="handleTagInputChange"
                        @keyup.enter.stop="handleTagInputEnter"
                        @keyup="handleTagInputChange"
                    >
                    <button
                        class="btn btn-sm btn-dark"
                        :disabled="isTagInputButtonDisabled || isCreatingTag"
                        @click.stop="handleTagInputEnter"
                    >
                        <font-awesome-icon :icon="['fas', 'plus']" />
                    </button>
                </div>

                <div class="d-flex flex-column justify-content-center">
                    <div v-if="null == availableTags">
                        <p>Loading...</p>
                    </div>
                    <div v-else-if="availableTags.length === 0 && pageStore.selectedPage.tags.length === 0">
                        <p>No tags found. <span class="bold">Create one</span> to start.</p>
                    </div>
                    <ul v-else class="nav nav-pills nav-fill d-flex flex-column gap-1">
                        <li v-if="pageStore.selectedPage" class="nav-item" v-for="tagPage in pageStore.selectedPage.tags">
                            <button
                                class="nav-link active d-flex flex-row gap-3 align-items-center"
                                @click="() => handleExistingTagDelete(tagPage)"
                            >
                                <span class="btn btn-sm" :style="{'background-color': tagPage.tag.color}">&nbsp;&nbsp;&nbsp;</span>
                                {{ tagPage.tag.name }}
                            </button>
                        </li>

                        <li class="nav-item" v-for="tag in availableTags" :key="tag.id">
                            <button
                                class="nav-link inactive d-flex flex-row gap-3 align-items-center"
                                @click="() => handleExistingTagAdd(tag)"
                            >
                                <span class="btn btn-sm" :style="{'background-color': tag.color}">&nbsp;&nbsp;&nbsp;</span>
                                {{ tag.name }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { defineProps, ref, onMounted, nextTick } from 'vue';
    import { fetchCreateTagPageFromTagId, fetchCreateTagPageFromTagName, fetchDeleteTagPage } from '@/fetch/TagFetcher.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const pageStore = usePageStore();
    const userStore = useUserStore();
    const availableTags = ref(null);
    const tagInput = ref(null);
    const isTagInputButtonDisabled = ref(true);
    const isCreatingTag = ref(false);

    const handleTagInputChange = () => {
        const tagInputValue = tagInput.value?.value;
        isTagInputButtonDisabled.value = !tagInputValue ? true : tagInputValue.trim().length === 0;

        reloadAvailableTags(); // filter them using the input value
    };

    const handleTagInputEnter = () => {
        isCreatingTag.value = true;

        fetchCreateTagPageFromTagName(props.page.id, tagInput.value.value).then((pageTab) => {
            pageStore.selectedPage.tags.push(pageTab);
            userStore.currentUser.selectedProject.tags.push(pageTab.tag);
            tagInput.value.value = ''; // Clear input to allow for another tag creation
            isCreatingTag.value = false;
            tagInput.value.focus();

            reloadAvailableTags();
        });
    };

    const handleExistingTagAdd = (tag) => {
        isCreatingTag.value = true;

        try {
            fetchCreateTagPageFromTagId(props.page.id, tag.id).then((pageTab) => {
                pageStore.selectedPage.tags.push(pageTab);
                isCreatingTag.value = false;

                reloadAvailableTags();
            });
        } catch (e) {
            isCreatingTag.value = false;
            console.error('Error creating tag page from existing tag', e);
        }
    };

     const handleExistingTagDelete = (tagPage) => {
        isCreatingTag.value = true;

        try {
            fetchDeleteTagPage(tagPage.id).then(() => {
                isCreatingTag.value = false;
                pageStore.selectedPage.tags = pageStore.selectedPage.tags.filter((tp) => tp.id !== tagPage.id);

                reloadAvailableTags();
            });
        } catch (e) {
            isCreatingTag.value = false;
            console.error('Error creating tag page from existing tag', e);
        }
    };

    onMounted(() => {
        reloadAvailableTags();
    });

    const reloadAvailableTags = () => {
        userStore.getCurrentUser().then(() => {
            availableTags.value = userStore.currentUser?.selectedProject?.tags;

            availableTags.value = availableTags.value.filter((tag) => {
                if (tagInput.value?.value?.trim().length > 0) {
                    console.log(tag.name.toLowerCase(), tagInput.value.value.toLowerCase());
                    if (!tag.name.toLowerCase().includes(tagInput.value.value.toLowerCase())) {
                        return false;
                    }
                }

                return !pageStore.selectedPage?.tags?.some((tagPage) => tagPage.tag.id === tag.id);
            });
        });
    };
</script>