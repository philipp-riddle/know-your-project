<template>
    <div class="d-flex flex-column gap-2">
        <div class="card">
            <div class="card-body d-flex flex-row gap-3 p-2">
                <UserBadge :user="threadItem.user" />
                <p class="m-0"><span v-html="threadItem.threadItemComment.comment"></span></p>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import UserBadge from '@/components/User/UserBadge.vue';

    const props = defineProps({
        threadItem: {
            type: Object,
            required: true,
        },
    });
</script>