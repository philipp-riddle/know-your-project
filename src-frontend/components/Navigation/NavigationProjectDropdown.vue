<template>
    <div>
        <VDropdown
            v-model:shown="isDropdownVisible"
        >
            <button
                class="nav-link btn btn-lg"
                v-tooltip="'Select and manage project'"
                :class="{
                    'dark-gray': isDropdownVisible,
                    'inactive': !isDropdownVisible,
                }"
            >
                <font-awesome-icon :icon="['fas', 'house']" />
            </button>

            <template #popper>
                <div class="p-2 d-flex flex-column gap-2" style="min-width: 15rem">
                    <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                        <p class="m-0">{{ projectStore.selectedProject.name }}</p>
                        <a href="/logout" v-tooltip="'Logout'" class="btn">
                            <font-awesome-icon :icon="['fas', 'right-from-bracket']" />
                        </a>
                    </div>

                </div>
            </template>
        </VDropdown>
    </div>
</template>

<script setup>
    import { ref } from 'vue'; 
    import TagDialogue from '@/components/Tag/TagDialogue.vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const projectStore = useProjectStore();
    const isDropdownVisible = ref(false);

    const onTagFilterRemove = (tag) => {
        filterStore.removeFilterTag(tag);
    };
</script>