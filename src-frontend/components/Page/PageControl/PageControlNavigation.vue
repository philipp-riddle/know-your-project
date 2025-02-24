<template>
    <div class="navigation-sidebar navigation-sidebar-right d-flex flex-row align-items-center justify-content-center gap-4">
        <!-- nav pills for page task controls -->
        <ul class="nav nav-pills d-flex flex-row justify-content-center align-items-center p-3 pt-0 pb-0 gap-3" v-if="page.task != null">
            <li class="nav-item">
                <TaskStatusControl :task="page.task" />
            </li>
        </ul>
        <ul class="nav nav-pills d-flex flex-row justify-content-center align-items-center p-3 pt-0 pb-0 gap-3" v-if="page.task != null">
            <li class="nav-item">
                <TaskDueDateControl :task="page.task" />
            </li>
        </ul>

        <!-- nav pills to delete and assign tags to page -->
        <ul class="nav nav-pills d-flex flex-row justify-content-center align-items-center p-3 pt-0 pb-0 gap-1">
            <li class="nav-item">
                <PageTagControl :page="page"/>
            </li>
        </ul>
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
    import PageTagControl from '@/components/Page/PageControl/PageTagControl.vue';
    import { usePageStore } from '@/stores/PageStore';

    const currentRoute = useRoute();
    const pageStore = usePageStore();
    const page = computed(() => pageStore.selectedPage);
</script>