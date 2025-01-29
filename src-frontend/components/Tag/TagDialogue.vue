<template>
    <div class="d-flex flex-row justify-content-between gap-1">
        <slot name="header"></slot>
    </div>

    <div
        v-if="showCreateControls || showSearchControls"
        class="d-flex flex-row justify-content-between gap-1 mb-3"
    >
        <input
            v-if="showSearchControls"
            type="text"
            class="form-control"
            :placeholder="showCreateControls ? 'Search or create...' : 'Search...'"
            tabIndex="1"
            ref="tagInput"
            style="z-index: 1000 !important;"
            @change="handleTagInputChange"
            @keyup.enter.stop="handleTagInputEnter"
            @keyup="handleTagInputChange"
        >
        <button
            v-if="showCreateControls"
            class="btn btn-sm btn-dark"
            :disabled="tagInput?.value.trim().length === 0"
            @click.stop="handleTagInputEnter"
        >
            <font-awesome-icon :icon="['fas', 'plus']" />
        </button>
    </div>

    <div class="d-flex flex-column justify-content-center">
        <div v-if="null == availableTags">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else-if="availableTags.length === 0 && tags.length === 0">
            <p>No tags found. <span class="bold">Create one</span> to start.</p>
        </div>
        <ul v-else class="nav nav-pills nav-fill d-flex flex-column gap-1">
            <!--  all already selected tags in this context -->
            <TagItem
                v-for="assignedTag in tags"
                :isActive="true"
                :tag="assignedTag"
                :showEditControls="showEditControls"
                :showNested="false"
                tooltip="Click to remove this tag"
                @click="(assignedTag) => $emit('removeTag', assignedTag)"
            />
            
            <!-- all available tags which are not yet assigned to this context -->
            <div v-for="tag in availableTags" class="flex-fill w-100">
                <TagItem
                    :tag="tag"
                    tooltip="Click to add this tag"
                    :showEditControls="showEditControls"
                    @click="(tag) => $emit('addTag', tag)"
                />
            </div>
        </ul>
    </div>
</template>

<script setup>
    import { computed, ref, watch, onMounted } from 'vue';
    import TagItem from '@/components/Tag/TagItem.vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const emit = defineEmits(['addTag', 'removeTag', 'createTag']);
    const props = defineProps({
        // The tags that are already assigned to the given context
        tags: {
            type: Array,
            required: true,
        },
        showEditControls: {
            type: Boolean,
            required: false,
            default: true,
        },
        showCreateControls: {
            type: Boolean,
            required: false,
            default: true,
        },
        showSearchControls: {
            type: Boolean,
            required: false,
            default: true,
        },
    });
    const projectStore = useProjectStore();
    const availableTags = ref(null);
    const tagInput = ref(null);
    const isTagInputButtonDisabled = ref(true);

    // this makes sure to reload the available tags when the component is mounted
    onMounted(async () => {
        reloadAvailableTags();
    });

    // this makes sure to always filter the available tags when the selected page changes
    watch (() => projectStore.selectedProject?.tags, () => {
        reloadAvailableTags();
    });

    // this makes sure to reload the available tags when the tags prop changes
    watch (() => props.tags, () => {
        reloadAvailableTags();
    }, { deep: true });

    // if the search / create input changes we must update the available tags list
    const handleTagInputChange = () => {
        reloadAvailableTags();
    };

    const handleTagInputEnter = () => {
        if (props.showCreateControls) { // only dispatch the event if the create controls are shown
            emit('createTag', tagInput.value.value);

            tagInput.value.value = '';
            tagInput.value.focus();
            reloadAvailableTags();
        }
    };

    const reloadAvailableTags = () => {
        projectStore.getSelectedProject().then((project) => {
            availableTags.value = project.tags;

            for (var i = 0; i < availableTags.value.length; i++) {
                const tag = availableTags.value[i];
                if (shouldFilterTag(tag)) {
                    availableTags.value = availableTags.value.filter((availableTag) => availableTag.id !== tag.id);
                    i--; // we need to decrement the index because we removed an element
                }
            }

            availableTags.value = availableTags.value.sort((a, b) => b.name - a.name);
        });
    };

    const shouldFilterTag = (tag) => {
        // if the tag has a parent we only want to show it in the parent context; not as an individual item.
        if (tag.parent !== null) {
            return true;
        }

        if (props.tags.some((existingTag) => existingTag.id === tag.id)) {
            return true;
        }

        // check if any of the tags childs are already assigned
        if (tag.tags.length > 0) {
            for (var i = 0; i < tag.tags.length; i++) {
                if (props.tags.some((existingTag) => existingTag.id === tag.tags[i].id)) {
                    return true;
                }
            }
        }

        // search has priority over everything
        if (tagInput.value.value.trim().length > 0) { // filter by search query if a search term is given
            return !tag.name.toLowerCase().includes(tagInput.value.value.toLowerCase());
        }

        return false;
    };
</script>