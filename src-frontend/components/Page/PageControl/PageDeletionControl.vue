<template>
    <DeletionButton
        label="page"
        :showTooltip="false"
        :darkMode="true"
        @onShowDropdown="isDropdownVisible = true"
        @onHideDropdown="isDropdownVisible = false"
        @onConfirm="onPageDelete"
    />
</template>

<script setup>
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useRouter } from 'vue-router';
    import { useRoute } from 'vue-router';
    import { ref } from 'vue';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const pageStore = usePageStore();
    const router = useRouter();
    const route = useRoute();
    const isDropdownVisible = ref(false);

    const onPageDelete = async () => {
        await pageStore.deletePage(props.page);

        // redirect to either Tasks or Wiki after page deletion; depending on the current route.
        if (route.name.includes('Wiki')) {
            router.push({ name: 'Wiki' });
        } else {
            router.push({ name: 'Tasks' });
        }
    };
</script>