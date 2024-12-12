<template>
    <div class="d-flex flex-column gap-2">
        <div class="d-flex flex-row align-items-center justify-content-between">
            <small class="text-muted" v-tooltip="'All your project pages and notes are displayed here.'"><strong>PAGES</strong></small>
            <NavigationCreateContentMenu />
        </div>
        <PageList />
    </div>
</template>

<script setup>
    import PageList from '@/components/Page/PageList.vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
</script>
