<template>
    <div class="container-fluid p-0 m-0 h-100 w-100">
        <div class="d-flex row h-100 p-0 m-0">
            <div class="h-100 col-sm-12 d-flex flex-column gap-4">
                <Navigation v-if="!isUserInSetup" />

                <router-view></router-view>
            </div>
        </div>
    </div>

    <!-- <ThreadBox /> -->
    <SearchModal />
</template>

<script setup>
    import { computed, onMounted } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import { useMagicKeys } from '@vueuse/core';
    import Navigation from '@/components/Navigation/Navigation.vue';
    import SearchModal from '@/components/Search/SearchModal.vue';
    import ThreadBox from '@/components/Thread/ThreadBox.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useSearchStore } from '@/stores/SearchStore.js';

    const currentRoute = useRoute();
    const router = useRouter();
    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const searchStore = useSearchStore();

    // specify the keys we want to monitor
    const { control, shift, meta, p, f } = useMagicKeys({
        passive: false,
        onEventFired(e) {
            const mainKeyIsPressed = control.value || shift.value || meta.value;
            const secondKeyIsPressed = p.value || f.value;

            if (mainKeyIsPressed && secondKeyIsPressed) {
                e.preventDefault();
                e.stopPropagation();
                searchStore.toggleIsSearching();
            }
        }
    });

    onMounted(() => {
        if (isUserInSetup.value && currentRoute.name !== 'Setup') {
            router.push({ name: 'Setup' });
        }
    });

    const isUserInSetup = computed(() => {
        return projectStore.selectedProject === null || currentRoute.name === 'Setup';
    });
</script>