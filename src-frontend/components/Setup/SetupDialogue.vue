<template>
    <div class="navigation-sidebar">
        <div class="row">
            <div class="col m-0 p-0 d-flex flex-row justify-content-center align-items-center gap-3">
                <button class="btn btn-dark-gray" @click="currentStep--" :disabled="currentStep === 1">
                    <font-awesome-icon :icon="['fas', 'chevron-left']" />
                </button>

                <ul class="nav nav-pills pt-4 pb-0 d-flex flex-row justify-content-center align-items-center gap-1">
                    <li v-for="step in setupSteps" :key="step.id" class="nav-item">
                        <button
                            class="nav-link btn"
                            :class="{
                                active: currentStep === step.id,
                                inactive: currentStep !== step.id,
                            }"
                            :disabled="currentMaximumStep < step.id"
                            @click="currentStep = step.id"
                        >
                            {{ step.name }}
                        </button>
                    </li>
                </ul>

                <button class="btn btn-dark-gray" @click="currentStep++" :disabled="currentStep >= currentMaximumStep">
                    <font-awesome-icon :icon="['fas', 'chevron-right']" />
                </button>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column align-items-center gap-3 mt-4">
        <SetupWelcome
            v-if="currentStep === 1"
            @continue="currentStep = 2"
        />
        <SetupProject
            v-else-if="currentStep === 2"
            @continue="currentStep = 3"
        />
        <SetupDone
            v-else-if="currentStep === 3"
            @continue="navigateToWiki"
        />
    </div>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import SetupDone from '@/components/Setup/SetupDone.vue';
    import SetupProject from '@/components/Setup/SetupProject.vue';
    import SetupWelcome from '@/components/Setup/SetupWelcome.vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const setupSteps = ref([
        {
            id: 1,
            name: 'Welcome',
        },
        {
            id: 2,
            name: 'Setting up project',
        },
        {
            id: 3,
            name: 'Done!',
        },
    ]);
    const projectStore = useProjectStore();

    const currentStep = ref(1);
    const currentMaximumStep = computed(() => {
        if (projectStore.selectedProject === null) {
            return 2; // cannot select past project step if no project is selected
        }

        return setupSteps.value.length;
    });

    const canNavigateToStep = (stepIndex) => {
        return stepIndex < 2 || projectStore.selectedProject !== null;
    };

    const navigateToWiki = () => {
        window.location.href = '#/wiki';
        window.location.reload();
    };
</script>