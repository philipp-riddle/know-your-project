<template>
    <TagItem
        :tag="tag"
        :tooltip="tooltip"
        :showEditControls="showEditControls"
        :isActive="isActive"
        :isNested="true"
        @add="onAdd"
        @click="onClick"
        @remove="onRemove"
    />
</template>

<script setup>
    import { computed } from 'vue';
    import TagItem from '@/components/Tag/TagItem.vue';
    import { useTagStore } from '@/stores/TagStore.js';

    const emit = defineEmits(['click', 'add', 'remove']);
    const props = defineProps({
        tag: {
            type: Object,
            required: false,
        },
        tagId: {
            type: Number,
            required: false,
            default: 0,
        },
        tooltip: {
            type: String,
            required: false,
            default: '',
        },
        isActive: {
            type: Boolean,
            required: false,
            default: false,
        },
        showEditControls: {
            type: Boolean,
            required: false,
            default: true,
        },
    });
    const tagStore = useTagStore();
    const tag = computed(() => props.tag ?? tagStore.tags.find((tag) => tag.id === props.tagId));

    const onClick = (tag) => {
        emit('click', tag);
    }

    const onAdd = (tag) => {
        emit('add', tag);
    }

    const onRemove = (tag) => {
        emit('remove', tag);
    }
</script>