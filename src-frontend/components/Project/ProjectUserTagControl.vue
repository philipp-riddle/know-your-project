<template>
    <VDropdown
        :placement="'bottom'"
    >
        <div class="tags-container d-flex flex-row align-items-center gap-2">
            <div class="d-flex justify-content-center">
                <button class="btn btn-sm m-0 p-0 text-muted d-flex flex-row gap-2" v-tooltip="'Click to add tags to user'">
                    <font-awesome-icon :icon="['fas', 'tags']" />
                    <span class="bold">TAGS</span>
                </button>
            </div>
            <div class="d-flex flex-row flex-wrap gap-2">
                <div v-if="projectUser.tags" v-for="tag in projectUser.tags">
                    <p class="m-0">
                        <span class="btn btn-sm me-2" :style="{'border-color': tag.tag.color, 'border-width': '2px'}">
                            {{ tag.tag.name }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <template #popper>
            <div class="p-2 d-flex flex-column gap-2">
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
                        <p>Loading... (no available tags)</p>
                    </div>
                    <div v-else-if="availableTags.length === 0 && projectUser.tags.length === 0">
                        <p>No tags found. <span class="bold">Create one</span> to start.</p>
                    </div>
                    <ul v-else class="nav nav-pills nav-fill d-flex flex-column gap-1">
                        <li v-if="projectUser.tags" class="nav-item" v-for="tagProjectUser in projectUser.tags" v-tooltip="'Click to remove this tag'">
                            <button
                                class="nav-link active d-flex flex-row gap-3 align-items-center"
                                @click="() => handleExistingTagDelete(tagProjectUser)"
                            >
                                <span class="btn btn-sm" :style="{'background-color': tagProjectUser.tag.color}">&nbsp;&nbsp;&nbsp;</span>
                                {{ tagProjectUser.tag.name }}
                            </button>
                        </li>

                        <li class="nav-item" v-for="tag in availableTags" :key="tag.id" v-tooltip="'Click to add this tag'">
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
    import { defineEmits, defineProps, ref, watch, onMounted } from 'vue';
    import { fetchCreateTagProjectUserFromTagId, fetchCreateTagProjectUserFromTagName, fetchDeleteTagProjectUser } from '@/fetch/TagFetcher.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const emit = defineEmits(['updateProjectUser']);
    const props = defineProps({
        projectUser: {
            type: Object,
            required: true,
        },
        project: {
            type: Object,
            required: true,
        },
    });
    const projectStore = useProjectStore();
    const availableTags = ref(null);
    const tagInput = ref(null);
    const isTagInputButtonDisabled = ref(true);
    const isCreatingTag = ref(false);

    // this makes sure to reload the available tags when the component is mounted
    onMounted(async () => {
        reloadAvailableTags();
    });

    const handleTagInputChange = () => {
        const tagInputValue = tagInput.value?.value;
        isTagInputButtonDisabled.value = !tagInputValue ? true : tagInputValue.trim().length === 0;

        reloadAvailableTags(); // filter them using the input value
    };

    const handleTagInputEnter = () => {
        isCreatingTag.value = true;

        fetchCreateTagProjectUserFromTagName(props.projectUser.id, tagInput.value.value).then((tagProjectUser) => {
            projectStore.selectedProject.tags.push(tagProjectUser.tag);
            props.projectUser.tags.push(tagProjectUser);
            emit('updateProjectUser', props.projectUser);

            tagInput.value.value = ''; // Clear input to allow for another tag creation
            isCreatingTag.value = false;
            tagInput.value.focus();

            reloadAvailableTags();
        });
    };

    const handleExistingTagAdd = (tag) => {
        isCreatingTag.value = true;

        try {
            fetchCreateTagProjectUserFromTagId(props.projectUser.id, tag.id).then((tagProjectUser) => {
                props.projectUser.tags.push(tagProjectUser);
                emit('updateProjectUser', props.projectUser);

                reloadAvailableTags();
            });
        } catch (e) {
            isCreatingTag.value = false;
            console.error('Error creating tag page from existing tag', e);
        }
    };

     const handleExistingTagDelete = (tagProjectUser) => {
        isCreatingTag.value = true;

        try {
            fetchDeleteTagProjectUser(tagProjectUser.id).then(() => {
                isCreatingTag.value = false;
                props.projectUser.tags = props.projectUser.tags.filter((tpu) => tpu.id !== tagProjectUser.id);
                emit('updateProjectUser', props.projectUser);

                reloadAvailableTags();
            });
        } catch (e) {
            isCreatingTag.value = false;
            console.error('Error creating tag page from existing tag', e);
        }
    };

    const reloadAvailableTags = () => {
        availableTags.value = props.project.tags;

        availableTags.value = availableTags.value.filter((tag) => {
            if (tagInput.value?.value?.trim().length > 0) {
                if (!tag.name.toLowerCase().includes(tagInput.value.value.toLowerCase())) {
                    return false;
                }
            }

            return !props.projectUser.tags?.some((tagUser) => tagUser.tag.id === tag.id);
        });
        availableTags.value = availableTags.value.sort((a, b) => b.name - a.name);
    };
</script>

<style scoped>
    .tags-container {
        cursor: pointer;
    }
</style>