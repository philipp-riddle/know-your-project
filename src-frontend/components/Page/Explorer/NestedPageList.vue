<template>
    <li class="nav-item d-flex flex-column gap-1">
        <p
            @click="toggleTag"
            class="nav-link nav-directory-link d-flex flex-row justify-content-between m-0"
            :class="{'active': isActive, 'inactive': !isActive}"
        >
            <span class="d-flex flex-row align-items-center gap-2">
                <font-awesome-icon :icon="['fas', 'chevron-'+(isActive ? 'down' : 'right')]" />
                <span>{{ tag.name }}</span>
            </span>
            <span class="btn btn-sm me-2" :style="{'background-color': tag.color}">&nbsp;&nbsp;&nbsp;</span>
        </p>

        <PageList
            v-if="isActive"
            :tag="tag"
            class="ms-3"
        />
    </li>
</template>
<script setup>
    import { computed, ref } from 'vue';
    import PageList from '@/components/Page/Explorer/PageList.vue';
    import { useTagStore } from '@/stores/TagStore.js';

    const props = defineProps({
        tag: {
            type: Object,
            required: false,
        },
        tagId: {
            type: Number,
            required: false,
        },
    });
    const tagStore = useTagStore();
    const tag = computed(() => props.tag ?? tagStore.tags.find((tag) => tag.id === props.tagId));

    const isActive = computed(() => tagStore.shownTags[tag.value.id] ?? false);

    const toggleTag = () => {
        if (tagStore.shownTags[tag.value.id] ?? null) {
            delete tagStore.shownTags[tag.value.id];
        } else {
            tagStore.shownTags[tag.value.id] = 1;
        }
    };
</script>