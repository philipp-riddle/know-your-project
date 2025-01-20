<template>
    <VDropdown
        :placement="'bottom'"
        v-model:shown="isDropdownVisible"
        :triggers="[]"
    >
        <button class="btn m-0 p-0" v-tooltip="'Click to add tags'" @click.stop="isDropdownVisible = !isDropdownVisible">
            <font-awesome-icon :icon="['fas', 'tags']" />
        </button>

        <template #popper>
            <div class="p-2 d-flex flex-column gap-2">
                <PageCreateTagControl :page="page" />
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref, watch } from 'vue';
    import PageTagUserControl from '@/components/Page/PageControl/Tag/PageTagUserControl.vue';
    import PageCreateTagControl from '@/components/Page/PageControl/Tag/PageCreateTagControl.vue';

    const emit = defineEmits(['showDropdown', 'hideDropdown']);
    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const isDropdownVisible = ref(false);

    watch(() => isDropdownVisible.value, async () => {
        if (isDropdownVisible.value) {
            emit('showDropdown');
        } else {
            emit('hideDropdown');
        }
    });
</script>