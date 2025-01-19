<template>
    <div
        class="nav-link d-flex flex-row align-items-center gap-2"
        :class="{
            'active': isActive,
            'inactive': !isActive,
            'active-outline': pageStore.selectedPage?.id == page.id && !isActive,
        }"
    >
        <font-awesome-icon
            v-if="page.user"
            :icon="['fas', 'lock']"
            v-tooltip="'Note - only you can see this.'"
        />
        <font-awesome-icon
            v-if="page.task"
            :icon="['fas', 'list-check']"
            v-tooltip="'This page belongs to a task.'"
        />
        <span
            class="nav-link p-1"
            :to="{ name: 'Page', params: { id: page.id } }"
            :class="{'active': isActive, 'inactive': !isActive}"
            @click="navigateToPage"
        >
            {{ page.name }}
        </span>
    </div>
</template>

<script setup>
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useRouter } from 'vue-router';
    import { useRoute } from 'vue-router';
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

        if (props.tag) {
            router.push({ name: 'PageTag', params: {id: props.page.id, tagName: props.tag.name}});
        } else {
            router.push({ name: 'Page', params: {id: props.page.id}});
        }
    };
</script>