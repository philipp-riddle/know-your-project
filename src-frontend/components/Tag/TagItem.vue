<template>
    <li
        class="nav-item"
        style="min-width: 20rem;"
        :class="{
            'nav-item-nested': isNested,
        }"
    >
        <div class=" d-flex flex-row justify-content-between align-items-center gap-2 w-100">
            <button
                v-if="!isEditing"
                v-tooltip="tooltip"
                class="flex-fill nav-link d-flex flex-row gap-2 justify-content-between align-items-center"
                :class="{'active': isActive, inactive: !isActive}"
                @click="() => onClick(tag)"
            >
                <div class="d-flex flex-row jus tify-content-between align-items-center gap-3">
                    <span v-if="isNested" class="btn btn-sm dark-gray">
                        <font-awesome-icon :icon="['fas', 'arrow-right']" />
                    </span>
                    <span v-else class="btn btn-sm" :style="{'background-color': (tag.parent ?? tag).color}">&nbsp;&nbsp;&nbsp;</span>

                    <div v-if="isActive" class="d-flex flex-column">
                        <h5 class="m-0">{{ tag.name }}</h5>
                        <div v-if="tag.parent !== null">
                            <p class="m-0 light-gray">{{ tag.parent.name }}</p>
                        </div>
                    </div>
                    <p v-else class="m-0">{{ tag.name }}</p>
                </div>
            </button>
            <div
                v-else
                class="flex-fill nav-link d-flex flex-column gap-2"
                :class="{'active': isActive}"
            >
                <EditTagControl
                    :tag="tag"
                    :isActive="isActive"
                    @enter="() => isEditing = false"
                />

                <CreateParentTagControl
                    v-if="isCreatingParent"
                    class="ms-5"
                    :tag="tag"
                    :isActive="isActive"
                    @create="isCreatingParent = false"
                />
            </div>

            <div v-if="showEditControls" class="d-flex flex-row align-items-center gap-2" :class="{'tag-edit-controls': !isEditing}">
                <button v-if="isEditing && !isActive" class="btn btn-sm" v-tooltip="'Add child tag'" @click.stop="isCreatingParent = !isCreatingParent">
                    <font-awesome-icon :icon="['fas', 'plus']" />
                </button>
                <DeletionButton
                    class="btn btn-tag-delete"
                    :class="{'editing': isEditing}"
                    label="tag"
                    :showTooltip="false"
                    :darkMode="true"
                    @onConfirm="onDeleteTag"
                    @onShowDropdown=""
                    @onHideDropdown=""
                />
                <button
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
            </div>
        </div>

        <NestedTagItem
            v-if="showNested"
            v-for="childTag in tag.tags"
            :tag="childTag"
            :tooltip="tooltip"
            :showEditControls="showEditControls"
            @add="onAdd"
            @click="onClick"
            @remove="onRemove"
        />
    </li>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import CreateParentTagControl from '@/components/Tag/CreateParentTagControl.vue';
    import EditTagControl from '@/components/Tag/EditTagControl.vue';
    import NestedTagItem from '@/components/Tag/NestedTagItem.vue';
    import { useTagStore } from '@/stores/TagStore.js';

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
        showNested: {
            type: Boolean,
            required: false,
            default: true,
        },
        isNested: {
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
    const tagStore = useTagStore();
    const isEditing = ref(false);
    const isCreatingParent = ref(false);

    const onDeleteTag = async () => {
        await tagStore.deleteTag(props.tag);
    };

    const onClick = (tag) => {
        emit('click', tag);
    };

    const onAdd = (tag) => {
        emit('add', tag);
    };

    const onRemove = (tag) => {
        emit('remove', tag);
    };

</script>

<style scoped lang="scss">
    .nav-item:hover .tag-edit-controls {
        opacity: 1;
        transition: opacity 0.2s ease-in-out;
    }

    .tag-edit-controls {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }

    .btn-tag-delete {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;

        &.editing {
            opacity: 1;
            transition: opacity 0.2s ease-in-out;
        }
    }
</style>