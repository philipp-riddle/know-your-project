<template>
    <li
        class="nav-item d-flex flex-row justify-content-between gap-2 w-100"
        style="min-width: 20rem;"
    >
        <button
            v-if="!isEditing"
            v-tooltip="tooltip"
            class="nav-link d-flex flex-row gap-2 justify-content-between align-items-center"
            :class="{'active': isActive, inactive: !isActive}"
            @click="$emit('click')"
        >
            <div class="d-flex flex-row justify-content-between align-items-center gap-3">
                <span class="btn btn-sm" :style="{'background-color': tag.color}">&nbsp;&nbsp;&nbsp;</span>
                {{ tag.name }}
            </div>

            <font-awesome-icon v-if="isActive" :icon="['fas', 'minus']" />
            <font-awesome-icon v-else :icon="['fas', 'plus']" />
        </button>
        <div
            v-else
            class="nav-link d-flex flex-row gap-2 justify-content-between align-items-center"
            :class="{'active': isActive}"
        >
            <PageEditTagControl
                :tag="tag"
                :isActive="isActive"
                @enter="() => isEditing = false"
            />
        </div>

        <button
            v-if="showEditControls"
            class="btn"
            :class="{
                'btn-tag-edit': !isEditing,
                'btn-dark-gray': !isEditing,
                'btn-dark': isEditing,
            }"
            @click.stop="isEditing = !isEditing"
        >
            <font-awesome-icon :icon="['fas', 'pen-to-square']" />
        </button>
    </li>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import PageEditTagControl from '@/components/Page/PageControl/Tag/PageEditTagControl.vue';

    const emit = defineEmits(['click', 'add', 'remove']);
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
        tooltip: {
            type: String,
            required: false,
            default: '',
        },
        showEditControls: {
            type: Boolean,
            required: false,
            default: true,
        },
    });
    const isEditing = ref(false);

</script>

<style scoped>
    .nav-item:hover .btn-tag-edit {
        opacity: 1;
        transition: opacity 0.2s ease-in-out;
    }

    .btn-tag-edit {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }
</style>