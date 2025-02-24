<template>
    <div class="d-flex flex-row align-items-center gap-1">
        <VDropdown>
            <button class="btn" v-tooltip="'Assign tags to this user'">
                <font-awesome-icon :icon="['fas', 'tag']" />
            </button>
            
            <template #popper>
                <div class="p-2">
                    <TagDialogue
                        :tags="currentUserTags"
                        @createTag="(tagName) => handleTagCreate(tagName)"
                        @addTag="(tag) => handleTagAdd(tag)"
                        @removeTag="(tag) => handleTagRemove(tag)"
                    />
                </div>
            </template>
        </VDropdown>

        <div class="d-flex flex-row flex-wrap gap-2">
            <button
                v-if="projectUser.tags"
                v-for="tag in projectUser.tags"
                class="btn btn-sm btn-tag"
                :style="{backgroundColor: tag.tag.color}"
                v-tooltip="tag.tag.name"
            >
                &nbsp;&nbsp;&nbsp;&nbsp;
            </button>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref, watch, onMounted } from 'vue';
    import TagDialogue from '@/components/Tag/TagDialogue.vue';
    import { fetchCreateTagProjectUserFromTagId, fetchCreateTagProjectUserFromTagName, fetchDeleteTagProjectUser } from '@/stores/fetch/TagFetcher.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useTagStore } from '@/stores/TagStore.js';

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
    const searchQuery = computed(() => {
        return tagInput.value?.value ?? '';
    });
    const projectStore = useProjectStore();
    const tagStore = useTagStore();
    const currentUserTags = ref(props.projectUser.tags.map((tagProjectUser) => tagProjectUser.tag));
    
    // whenever the project user tags changes, the assigned page tags must change as well
    watch(() => props.projectUser.tags, (newProjectUserTags) => {
        currentUserTags.value = newProjectUserTags.map((tagProjectUser) => tagProjectUser.tag);
    }, {deep: true});

    const handleTagCreate = (tagName) => {
        fetchCreateTagProjectUserFromTagName(props.projectUser.id, tagName).then((tagProjectUser) => {
            tagStore.addTag(tagProjectUser.tag);
            props.projectUser.tags.push(tagProjectUser);
            emit('updateProjectUser', props.projectUser);
        });
    };

    const handleTagAdd = (tag) => {
        fetchCreateTagProjectUserFromTagId(props.projectUser.id, tag.id).then((tagProjectUser) => {
            props.projectUser.tags.push(tagProjectUser);
            emit('updateProjectUser', props.projectUser);
        });
    };

    const handleTagRemove = (tag) => {
        // find the applied tag project user in the user object.
        // the tag dialogue only gives us the general tag object / ID here.
        const tagProjectUser = props.projectUser.tags.find((tagProjectUser) => tagProjectUser.tag.id === tag.id);

        if (null === tagProjectUser) {
            console.error('Tag project user not found in project user object; cannot delete');
            return;
        }

        fetchDeleteTagProjectUser(tagProjectUser.id).then(() => {
            props.projectUser.tags = props.projectUser.tags.filter((tpu) => tpu.id !== tagProjectUser.id);
            emit('updateProjectUser', props.projectUser);
        });
    };
</script>