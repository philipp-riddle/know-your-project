<template>
    <div class="navigation-sidebar navigation-sidebar-right d-flex flex-row align-items-center justify-content-center gap-4">
        <PageTextEditorControl />

        <ul class="nav nav-pills d-flex flex-row justify-content-center align-items-center p-3 pt-0 pb-0 gap-1">
            <li class="nav-item">
                <PageDeletionControl :page="page" />
            </li>
        </ul>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import { useRoute } from 'vue-router';

    import TaskStatusControl from '@/components/Page/PageControl/TaskStatusControl.vue';
    import TaskDueDateControl from '@/components/Page/PageControl/TaskDueDateControl.vue';
    import PageDeletionControl from '@/components/Page/PageControl/PageDeletionControl.vue';
    import PageTextEditorControl from '@/components/Page/PageControl/PageTextEditorControl.vue';
    import { usePageStore } from '@/stores/PageStore';
    import { usePageSectionStore } from '@/stores/PageSectionStore';

    const currentRoute = useRoute();
    const pageStore = usePageStore();
    const page = computed(() => pageStore.selectedPage);
    const pageSectionStore = usePageSectionStore();
</script>