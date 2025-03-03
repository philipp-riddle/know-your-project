<template>
    <div class="container-fluid p-0 m-0 h-100 w-100">
        <div class="d-flex row h-100 p-0 m-0">
            <div class="h-100 col-sm-12 d-flex flex-column gap-4">
                <Navigation v-if="!isUserInSetup" />

                <router-view></router-view>
            </div>
        </div>
    </div>

    <SearchModal />

    <ToastBox />
</template>

<script setup>
    import { computed, watch } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import { useMagicKeys } from '@vueuse/core';
    import Navigation from '@/components/Navigation/Navigation.vue';
    import SearchModal from '@/components/Search/SearchModal.vue';
    import ToastBox from '@/components/Util/Toast/ToastBox.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useSearchStore } from '@/stores/SearchStore.js';

    const currentRoute = useRoute();
    const router = useRouter();
    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const searchStore = useSearchStore();

    // specify the keys we want to monitor globally (=> HOTKEYS)
    const { control, meta, p, f } = useMagicKeys({
        passive: false,
        onEventFired(e) {
            // either command or control is pressed; the "main key"
            const mainKeyIsPressed = control.value || meta.value;
            // either p or f is pressed; the "second key"
            const secondKeyIsPressed = p.value || f.value;

            if (mainKeyIsPressed && secondKeyIsPressed) {
                e.preventDefault();
                e.stopPropagation();
                searchStore.toggleIsSearching();
            }
        }
    });

    watch(() => currentRoute.name, (newRoute) => {
        if (!newRoute) {
            return;
        }

        if (isUserInSetup.value && newRoute !== 'Setup') {
            router.push({ name: 'Setup' }); // navigate any users who must go through setup to the setup page
        } else if (!isUserInSetup.value && newRoute === 'Setup') {
            router.push({ name: 'Wiki' }); // navigate any users who have completed setup to the wiki page
        }
    });

    const isUserInSetup = computed(() => {
        return projectStore.selectedProject === null;
    });
</script>