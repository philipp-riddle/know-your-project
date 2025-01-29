<template>
    <div class="me-4 d-flex flex-row gap-2 align-items-center">
        <input
            type="text"
            class="flex-fill magic-input m-0 p-0"
            :class="{
                'white': isActive,
                'dark-gray': !isActive,
            }"
            placeholder="Enter child tag name..."
            tabIndex="1"
            style="z-index: 1000 !important;"
            @keyup.enter.stop="onPressEnter"
            ref="tagNameInput"
        >
        <button
            class="btn btn-sm btn-dark"
            :disabled="!canSubmit"
        >
            <div v-if="isCreateLoading" class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <font-awesome-icon v-else :icon="['fas', 'plus']" />
        </button>
    </div>
</template>

<script setup>
    import { computed, onMounted, ref } from 'vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useTagStore } from '@/stores/TagStore.js';

    const emit = defineEmits(['create']);
    const props = defineProps({
        tag: {
            type: Object,
            required: true,
        },
        isActive: {
            type: Boolean,
            required: false,
            default: false,
        },
    });
    const projectStore = useProjectStore();
    const tagStore = useTagStore();
    const tag = ref(props.tag);
    const tagNameInput = ref(null);
    const isCreateLoading = ref(false);

    const canSubmit = computed(() => tagNameInput.value?.value.trim().length > 0);

    onMounted(() => {
        tagNameInput.value.focus();
    });

    // create a parent tag, associated with the selected project, the new name and the current tag as parent
    const onPressEnter = () => {
        isCreateLoading.value = true;
        tagStore.createTag(projectStore.selectedProject, tagNameInput.value.value, props.tag).then((parentTag) => {
            emit('create', parentTag);
        }).finally(() => {
            isCreateLoading.value = false;
        });
    };
</script>