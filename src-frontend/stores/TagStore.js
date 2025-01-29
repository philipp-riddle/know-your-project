import { defineStore } from 'pinia';
import { useFilterStore  } from './FilterStore';
import { usePageStore  } from './PageStore';
import { useProjectStore } from './ProjectStore';
import { ref } from 'vue';
import { fetchCreateTag, fetchDeleteTag } from '@/stores/fetch/TagFetcher';

export const useTagStore = defineStore('tag', () => {
    const filterStore = useFilterStore();
    const pageStore = usePageStore();
    const projectStore = useProjectStore();

    /**
     * Global store for the tags of a project.
     */
    const projectTags = ref([]);

    const createTag = (project, tagName, parentTag) => {
        return fetchCreateTag(project.id, tagName, parentTag?.id).then((createdTag) => {
            projectStore.selectedProject.tags.push(createdTag); // add the tag to the project

            projectStore.selectedProject.tags = projectStore.selectedProject.tags.map((tag) => {
                if (tag.id === parentTag.id) {
                    tag.tags.push(createdTag); // push it to the parent tag; this way it can be displayed nested in the frontend
                }

                return tag;
            })
        });
    };

    const deleteTag = (tag) => {
        return fetchDeleteTag(tag).then(() => {
            projectTags.value = projectTags.value.filter((t) => t.id !== tag.id); // make sure the tag is not included in the global store for the tags anymore
            filterStore.removeFilterTag(tag); // make sure the tag is not included in the filters anymore
            pageStore.removeTag(tag); // make sure the tag is not included in the displayed page and page lists anymore
            projectStore.selectedProject.tags = projectStore.selectedProject.tags.filter((t) => t.id !== tag.id); // make sure the tag is not included in the project anymore
        });
    };

    return {
        projectTags,
        createTag,
        deleteTag,
    };
});