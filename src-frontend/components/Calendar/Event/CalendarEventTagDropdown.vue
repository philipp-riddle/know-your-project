<template>
    <div>
        <div class="d-flex flex-row align-items-center gap-2">
            <button class="btn btn-dark-gray m-0" v-tooltip="'Back to event creation'" @click="emit('back')">
                <font-awesome-icon :icon="['fa', 'chevron-left']" />
            </button>
            <h5 class="m-0 dark=gray">{{ eventName }}</h5>
        </div>
        <TagDialogue
            :tags="tags"
            @addTag="(tag) => handleTagAdd(tag)"
            @removeTag="(tag) => handleTagRemove(tag)"
            :showCreateControls="false"
        />
    </div>
</template>

<script setup>
    import { ref, watch } from 'vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import TagDialogue from '@/components/Tag/TagDialogue.vue';

    const emit = defineEmits(['update', 'back']);
    const props = defineProps({
        eventName: {
            type: String,
            required: true,
        },
        tags: {
            type: Array,
            required: true,
        },
    });
    const tags = ref(props.tags);

    const handleTagAdd = (tag) => {
        tags.value.push(tag);
        emit('update', tags.value);
    };

    const handleTagRemove = (tag) => {
        tags.value = tags.value.filter((t) => t.id !== tag.id);
        emit('update', tags.value);
    };
</script>