<template>
    <draggable
        class="nav nav-pills nav-fill d-flex flex-column p-0 m-0"
        v-model="displayedTags"
        item-key="id"
        tag="ul"
        @end="onDragEnd"
    >
        <template #item="{ element }">
            <li :tag="element.id" class="nav-item d-flex flex-column gap-1">
                <p
                    @click="() => toggleTag(element.id)"
                    class="nav-link nav-directory-link d-flex flex-row justify-content-between m-0"
                    :class="{'active': tagStore.shownTags[element.id], 'inactive': !tagStore.shownTags[element.id]}"
                >
                    <span class="d-flex flex-row align-items-center gap-2">
                        <font-awesome-icon :icon="['fas', 'chevron-'+(tagStore.shownTags[element.id] ? 'down' : 'right')]" />
                        <span>{{ element.name }}</span>
                    </span>
                    <TagBadge
                        :tag="element"
                        size="lg"
                    />
                </p>

                <PageList
                    v-if="tagStore.shownTags[element.id] == true"
                    :tag="element"
                    class="ms-3"
                />
            </li>
        </template>
    </draggable>
</template>
<script setup>
    import { computed, ref, onMounted, watch } from 'vue';
	import draggable from "vuedraggable";
    import PageList from '@/components/Page/Explorer/PageList.vue';
    import TagBadge from '@/components/Tag/TagBadge.vue';
    import { useTagStore } from '@/stores/TagStore.js';

    const props = defineProps({
        tagIds: {
            type: Array,
            required: true,
        }
    });
    const tagStore = useTagStore();

    // enriched copy of nested tag IDs - vuedraggable modifies this variable when reordering, that's why we need a copy.
    // if the tag store changes, we need to update this list as well.
    const displayedTags = ref(null);

    watch(() => tagStore.tags, () => {
        displayedTags.value = props.tagIds.map((tagId) => tagStore.tags.filter((tag) => tag.id == tagId)[0] ?? null);
    }, {deep: true});

    onMounted(() => {
        displayedTags.value = props.tagIds.map((tagId) => tagStore.tags.filter((tag) => tag.id == tagId)[0] ?? null);
    })

    const toggleTag = (tagId) => {
        if (tagStore.shownTags[tagId] ?? null) {
            delete tagStore.shownTags[tagId];
        } else {
            tagStore.shownTags[tagId] = 1;
        }
    };

    const onDragEnd = (event) => {
        const parentTag = displayedTags.value[0].parent ?? null;
        let tagIdOrder = [];

        for (let i = 0; i < event.to.children.length; i++) {
            let tagId = event.to.children[i].getAttribute('tag');

            if (isNaN(tagId)) {
                console.error('Invalid page ID in PageList.vue component!', event.to.children[i]);
                continue; // this is to prevent us from including any corrupted data in the list.
            }

            tagIdOrder.push(parseInt(tagId));
        }

        tagStore.reorderTags(parentTag?.id, tagIdOrder);
    };
</script>