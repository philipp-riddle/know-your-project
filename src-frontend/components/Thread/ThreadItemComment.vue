<template>
    <div class="d-flex flex-column gap-2">
        <div class="card">
            <div class="card-header p-2">
                <span v-html="threadItem.user.email"></span>
            </div>
            <div class="card-body p-2">
                <span v-html="threadItem.threadItemComment.comment"></span>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        threadItem: {
            type: Object,
            required: true,
        },
    });
</script>