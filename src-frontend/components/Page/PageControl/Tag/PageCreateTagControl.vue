<template>
    <div class="d-flex flex-row justify-content-between gap-1">
        <input
            type="text"
            class="form-control"
            placeholder="Search or create..."
            tabIndex="1"
            ref="tagInput"
            style="z-index: 1000 !important;"
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
            <div class="spinner-border mt-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else-if="availableTags.length === 0 && pageStore.selectedPage.tags.length === 0">
            <p>No tags found. <span class="bold">Create one</span> to start.</p>
        </div>
        <ul v-else class="nav nav-pills nav-fill d-flex flex-column gap-1">
            <!--  all already selected tags -->
            <TagItem
                v-for="tagPage in pageStore.selectedPage.tags"
                :isActive="true"
                :tag="tagPage.tag"
                tooltip="Click to remove this tag"
                @click="() => handleExistingTagDelete(tagPage)"
            />
            
            <!-- all available tags which are not yet assigned to this page -->
            <TagItem
                v-for="tag in availableTags"
                :tag="tag"
                tooltip="Click to add this tag"
                @click="() => handleExistingTagAdd(tag)"
            />
        </ul>
    </div>
</template>

<script setup>
    import { ref, watch, onMounted } from 'vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import PageTagUserControl from '@/components/Page/PageControl/Tag/PageTagUserControl.vue';
    import TagItem from '@/components/Page/PageControl/Tag/TagItem.vue';

    const emit = defineEmits(['showDropdown', 'hideDropdown']);
    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        onSelectTag: {
            type: Function,
            required: true,
        },
    });
    const pageStore = usePageStore();
    const userStore = useUserStore();
    const availableTags = ref(null);
    const tagInput = ref(null);
    const isTagInputButtonDisabled = ref(true);
    const isCreatingTag = ref(false);

    // this makes sure to reload the available tags when the component is mounted
    onMounted(async () => {
        reloadAvailableTags();
    });

    // this makes sure to always filter the available tags when the selected page changes
    watch (() => pageStore.selectedPage, () => {
        reloadAvailableTags();
    });

    const handleTagInputChange = () => {
        const tagInputValue = tagInput.value?.value;
        isTagInputButtonDisabled.value = !tagInputValue ? true : tagInputValue.trim().length === 0;

        reloadAvailableTags(); // filter them using the input value
    };

    const handleTagInputEnter = () => {
        isCreatingTag.value = true;

        pageStore.addTagToPageByName(props.page, tagInput.value.value).then((pageTag) => {
            userStore.currentUser.selectedProject.tags.push(pageTag.tag);
            tagInput.value.value = ''; // Clear input to allow for another tag creation
            isCreatingTag.value = false;
            tagInput.value.focus();

            reloadAvailableTags();
        });
    };

    const handleExistingTagAdd = (tag) => {
        isCreatingTag.value = true;

        try {
            pageStore.addTagToPageById(props.page, tag.id).then((pageTag) => {
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
            pageStore.removeTagFromPage(props.page, tagPage).then(() => {
                isCreatingTag.value = false;
                reloadAvailableTags();
            });
        } catch (e) {
            isCreatingTag.value = false;
            console.error('Error creating tag page from existing tag', e);
        }
    };

    const reloadAvailableTags = () => {
        if (!pageStore.selectedPage) {
            return;
        }

        // @todo sorting does not work here
        pageStore.selectedPage.tags = pageStore.selectedPage.tags.sort((a, b) => b.name - a.name);

        userStore.getCurrentUser().then(() => {
            availableTags.value = userStore.currentUser?.selectedProject?.tags;

            availableTags.value = availableTags.value.filter((tag) => {
                if (tagInput.value?.value?.trim().length > 0) {
                    if (!tag.name.toLowerCase().includes(tagInput.value.value.toLowerCase())) {
                        return false;
                    }
                }

                return !pageStore.selectedPage?.tags?.some((tagPage) => tagPage.tag.id === tag.id);
            });
            availableTags.value = availableTags.value.sort((a, b) => b.name - a.name);
        });
    };
</script>