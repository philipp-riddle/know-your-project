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
    import { defineProps } from 'vue';
    import TagItem from '@/components/Tag/TagItem.vue';

    const emit = defineEmits(['click', 'add', 'remove']);
    const props = defineProps({
        tag: {
            type: Object,
            required: true,
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