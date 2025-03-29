<template>
    <button
        class="btn m-0 w-100 p-2 flex-fill d-flex flex-row align-items-center gap-2"
        @click="() => openEventModal(event)"
    >
        <span class="btn btn-lg m-0 p-1 btn-dark-gray" v-tooltip="'This calendar entry is related to a task due date.'">
            <font-awesome-icon :icon="['fas', 'list-check']" />
        </span>
        <div class="d-flex flex-column align-items-start justify-content-start gap-1">
            <p class="m-0" style="text-align: left;">{{ event.page.name }}</p>
            <TagBadge 
                v-for="eventTag in event.page.tags"
                :key="eventTag.id"
                :tag="eventTag.tag"
            />
        </div>
    </button>
</template>

<script setup>
    import { useRouter } from 'vue-router';
    import TagBadge from '@/components/Tag/TagBadge.vue';

    const props = defineProps({
        event: {
            type: Object,
            required: true,
        },
    });
    const router = useRouter();

    const openEventModal = (event) => {
        if (event.page) { // is a task
            router.push({ name: 'CalendarPage', params: {id: event.id}});
        }
    }
</script>