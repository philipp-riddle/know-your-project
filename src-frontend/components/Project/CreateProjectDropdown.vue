<template>
    <VDropdown>
        <ul class="nav calendar-nav nav-pills d-flex flex-row justify-content-center align-items-center gap-3">
            <li class="nav-item">
                <button class="nav-link btn btn-dark d-flex flex-row gap-3">
                    <font-awesome-icon :icon="['fas', 'plus']" />
                    Create project
                </button>
            </li>
        </ul>
        
        <template #popper>
            <div class="p-2 d-flex flex-row justify-content-between gap-2" style="min-width: 15rem">
                <input
                    type="text"
                    class="form-control"
                    id="projectName"
                    v-model="projectName"
                    placeholder="e.g. Design workforce"
                >
                <button class="btn btn-dark-gray" :disabled="!canSubmitProject" @click="submitProject">
                    <font-awesome-icon :icon="['fas', 'circle-plus']" />
                </button>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import { useProjectStore } from '@/stores/ProjectStore';

    const projectStore = useProjectStore();
    const projectName = ref('');

    const canSubmitProject = computed(() => {
        return projectName.value.trim() !== ''
    });

    const submitProject = () => {
        if (!canSubmitProject.value) {
            return;
        }

        projectStore.createProject(projectName.value.trim());
    }
</script>