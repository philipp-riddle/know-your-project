<template>
    <div class="d-flex flex-row gap-3 align-items-start justify-content-between thread-item">
        <div class="col-sm-10">
            <ThreadItemComment
                v-if="threadItem.threadItemComment" :threadItem="threadItem"
            />
            <ThreadItemPrompt
                v-else-if="threadItem.itemPrompt"
                :threadItem="threadItem"
            />
            <div v-else>
                <p class="text-danger">Cannot display thread item.</p>
            </div>
        </div>
        <div class="thread-options" v-if="canDeleteThreadItem">
            <button class="btn btn-sm m-0 p-0" :disabled="isDeleting" @click="onDelete" v-tooltip="'Delete thread item'">
                <font-awesome-icon :icon="['fas', 'trash']" />
            </button>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import ThreadItemComment from '@/components/Thread/ThreadItemComment.vue';
    import ThreadItemPrompt from '@/components/Thread/ThreadItemPrompt.vue';
    import { useThreadStore } from '@/stores/ThreadStore.js';
    import { useUserStore } from '@/stores/UserStore.js';

    const props = defineProps({
        threadItem: {
            type: Object,
            required: true,
        },
    });
    const isDeleting = ref(false);
    const userStore = useUserStore();
    const threadStore = useThreadStore();

    const canDeleteThreadItem = computed(() => {
        return props.threadItem.user.id === userStore.currentUser.id;
    });

    const onDelete = () => {
        isDeleting.value = true;
        threadStore.deleteThreadItem(props.threadItem).then(() => {
            isDeleting.value = false;
        });
    };
</script>

<style scoped>
    .thread-options {
        display: none;
    }

    .thread-item:hover .thread-options {
        display: block;
    }
</style>