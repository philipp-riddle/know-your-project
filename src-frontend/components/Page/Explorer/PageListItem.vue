<template>
    <div
        :page="page.id"
        :tag="tag?.id"
        :name="page.name"
        class="nav-link d-flex flex-row align-items-center gap-2"
        :class="{
            'active': isActive,
            'inactive': !isActive,
            'active-outline': pageStore.selectedPage?.id == page.id && !isActive,
        }"
        @click="navigateToPage"
    >
        <font-awesome-icon
            v-if="page.task"
            :icon="['fas', 'list-check']"
            v-tooltip="'This page belongs to a task.'"
        />
        <!-- for accessibility also a link -->
        <router-link
            :to="{ name: 'WikiPage', params: { id: page.id } }"
            class="nav-link p-1"
            :class="{'active': isActive, 'inactive': !isActive}"
            @click="navigateToPage"
        >
            {{ page.name }}
        </router-link>
    </div>
</template>

<script setup>
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useRoute, useRouter } from 'vue-router';
    import { computed } from 'vue';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        tag: {
            type: Object,
            required: false,
        },
        onPageDelete: {
            type: Function,
            required: false,
        },
    });
    const pageStore = usePageStore();
    const router = useRouter();
    const route = useRoute();
    
    const isActive = computed(() => {
        const isTagNameMatching = route.params.tagName && props.tag ? route.params.tagName == props.tag.name : true;
        const isPageIdMatching = pageStore.selectedPage?.id == props.page.id;

        return isTagNameMatching && isPageIdMatching;
    });

    const navigateToPage = () => {
        pageStore.setSelectedPage(props.page);
        router.push({ name: 'WikiPage', params: {id: props.page.id}});
    };
</script>