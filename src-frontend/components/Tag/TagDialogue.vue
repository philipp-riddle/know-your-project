<template>
    <div class="d-flex flex-row justify-content-between gap-1">
        <slot name="header"></slot>
    </div>

    <div
        v-if="showCreateControls || showSearchControls"
        class="d-flex flex-row justify-content-between gap-1"
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
                tooltip="Click to remove this tag"
                :showEditControls="showEditControls"
                @click="$emit('removeTag', assignedTag)"
            />
            
            <!-- all available tags which are not yet assigned to this context -->
            <TagItem
                v-for="tag in availableTags"
                :tag="tag"
                tooltip="Click to add this tag"
                :showEditControls="showEditControls"
                @click="$emit('addTag', tag)"
            />
        </ul>
    </div>
</template>

<script setup>
    import { computed, ref, watch, onMounted } from 'vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import TagItem from '@/components/Page/PageControl/Tag/TagItem.vue';

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

            availableTags.value = availableTags.value.filter((tag) => {
                if (tagInput.value.value.trim().length > 0) { // filter by search query if a search term is given
                    if (!tag.name.toLowerCase().includes(tagInput.value.value.toLowerCase())) {
                        return false;
                    }
                }

                return !props.tags.some((existingTag) => existingTag.id === tag.id);
            });

            availableTags.value = availableTags.value.sort((a, b) => b.name - a.name);
        });
    };
</script>