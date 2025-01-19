<template>
   <div
        :class="{
            // if the dropdown is open the control should be completely visible and not only on hover
            'section-options': !isDropdownVisible,
        }"
    >
        <DeletionButton
            label="page"
            :showTooltip="false"
            @onShowDropdown="isDropdownVisible = true"
            @onHideDropdown="isDropdownVisible = false"
            @onConfirm="onPageDelete"
        />
   </div>
</template>

<script setup>
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useRouter } from 'vue-router';
    import { ref } from 'vue';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const pageStore = usePageStore();
    const router = useRouter();
    const isDropdownVisible = ref(false);

    const onPageDelete = async () => {
        await pageStore.deletePage(props.page);

        router.push({ name: 'Tasks' }); // redirect to tasks page after deletion
    };
</script>