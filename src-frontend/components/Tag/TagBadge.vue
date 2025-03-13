<template>
    <button
        class="btn btn-tag p-1"
        :class="tagClass"
        v-tooltip="tag.name"
    >
        <font-awesome-icon
            :style="{color: tag.color}"
            :icon="['fas', 'diamond']"
        />
    </button>
</template>

<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        tag: {
            type: Object,
            required: true,
        },
        tagWrapperEntity: { // e.g. tag associated with user, page, ...
            type: Object,
            required: false,
        },
        size: {
            type: String,
            required: false,
        },
    });

    const tagClass = computed(() => {
        if (!props.size) {
            return {};
        }

        return {
            'btn-sm': props.size === 'sm',
            'btn-lg': props.size === 'lg',
        };
    });
</script>