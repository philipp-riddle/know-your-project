<template>
    <div class="d-flex flex-row justify-content-between align-items-center gap-3">
        <div>
            <input
                type="color"
                v-model="tag.color"
                ref="tagColorInput"
                style="display: none;"
                @change="handleTagUpdate"
                @keyup.enter.stop="$emit('enter')"
                @click="handleTagUpdate"
            >
            <TagBadge
                :tag="tag"
                size="lg"
                v-tooltip="'Change color'"
                @click="toggleColorpicker"
            />
        </div>
        <input
            type="text"
            class="magic-input m-0 p-0"
            :class="{
                'white': isActive,
                'dark-gray': !isActive,
            }"
            v-model="tag.name"
            placeholder="Enter tag name..."
            tabIndex="1"
            style="z-index: 1000 !important;"
            @keyup.enter.stop="$emit('enter')"
            ref="tagNameInput"
        >
    </div>
</template>

<script setup>
    import { onMounted, ref, watch } from 'vue';
    import { useDebounceFn } from '@vueuse/core';
    import TagBadge from '@/components/Tag/TagBadge.vue';
    import { useTagStore } from '@/stores/TagStore';

    const emit = defineEmits(['enter']);
    const props = defineProps({
        tag: {
            type: Object,
            required: true,
        },
        isActive: {
            type: Boolean,
            required: true,
        },
    });
    const tagStore = useTagStore();
    const tag = ref(props.tag);
    const tagNameInput = ref(null);
    const tagColorInput = ref(null);
    const isColorpickerVisible = ref(false);

    onMounted(() => {
        tagNameInput.value.focus();
    });

    // whenever any of the tag properties change, update the tag
    watch(() => tag.value, () => {
        debouncedTagUpdate(tag.value);
    }, {deep: true})

    const debouncedTagUpdate = useDebounceFn((tag) => {
        tagStore.updateTag(tag);
    }, 300);

    const toggleColorpicker = () => {
        if (isColorpickerVisible.value) {
            isColorpickerVisible.value = false;
            tagColorInput.value.blur();
        } else {
            isColorpickerVisible.value = true;
            tagColorInput.value.click(); // this opens the hidden color picker input
        }
    };
</script>