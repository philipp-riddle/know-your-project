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
                <PageCreateTagControl
                    v-if="selectedTag == null"
                    :page="page"
                    :onSelectTag="(tag) => selectedTag = tag"
                />
                <PageEditTagControl
                    v-else
                    :tag="selectedTag"
                    @close="selectedTag = null"
                />
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref, watch, onMounted, nextTick } from 'vue';
    import PageTagUserControl from '@/components/Page/PageControl/Tag/PageTagUserControl.vue';
    import PageCreateTagControl from '@/components/Page/PageControl/Tag/PageCreateTagControl.vue';
    import PageEditTagControl from '@/components/Page/PageControl/Tag/PageEditTagControl.vue';

    const emit = defineEmits(['showDropdown', 'hideDropdown']);
    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const isDropdownVisible = ref(false);
    const selectedTag = ref(null); // users can select a tag to edit it - name, color (or delete it)

    watch(() => isDropdownVisible.value, async () => {
        if (isDropdownVisible.value) {
            emit('showDropdown');
        } else {
            emit('hideDropdown');

            // wait for the next tick so that the user does not see the tag dropdown closing before the selected tag is reset
            // @todo does not work
            await nextTick();
            selectedTag.value = null; // if the tag dropdown is closed we reset the selected tag 
        }
    });
</script>