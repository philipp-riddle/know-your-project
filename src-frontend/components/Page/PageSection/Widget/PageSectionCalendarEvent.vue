<template>
    <div class="card section-card section-card-small w-100 ">
        <div class="card-body p-4">
            <div v-if="pageSection.calendarEvent.calendarEvent" class="d-flex flex-row align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="m-0">{{ pageSection.calendarEvent?.calendarEvent?.name }}</h4>
                    <p class="text-muted">{{ eventDate }}</p>
                </div>
                <button class="btn m-0 btn-dark-gray" @click="resetEvent" v-tooltip="'Reset and connect another event'">
                    <font-awesome-icon :icon="['fas', 'times']" />
                </button>
            </div>
            <div v-else>
                <h4 class="m-0">Connect calendar event</h4>
                <input
                    type="text"
                    class="form-control"
                    placeholder="Search for event"
                >
                <div class="d-flex flex-column gap-2">
                    <button
                        class="btn p-2 d-flex flex-row align-items-center gap-2"
                        v-for="event in events"
                        :key="event.id"
                        @click="() => selectEvent(event)"
                    >
                        <p class="m-0">{{ event.name }}</p>
                        <div v-if="event.eventTags">
                            <span
                                class="btn btn-sm btn-tag"
                                v-for="eventTag in event.eventTags"
                                :key="eventTag.id"
                                v-tooltip="eventTag.tag.name"
                                :style="{'background-color': eventTag.tag.color}"
                            >
                                &nbsp;&nbsp;&nbsp;
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed, onMounted, ref } from 'vue';
    import { useDateConverter } from '@/composables/DateConverter';
    import { useDateFormatter } from '@/composables/DateFormatter';
    import { fetchEventList } from '@/stores/fetch/CalendarFetcher';
    import { useProjectStore } from '@/stores/ProjectStore';

    const props = defineProps({
        pageSection: { // this prop is only set if we have an already existing section
            type: Object,
            required: true,
        },
        onPageSectionSubmit: {
            type: Function,
            required: true,
        },
    });
    const dateConverter = useDateConverter();
    const dateFormatter = useDateFormatter();
    const projectStore = useProjectStore();

    const events = ref([]);

    const eventDate = computed(() => {
        const localDate = dateConverter.convertUTCToLocalDateString(props.pageSection.calendarEvent.calendarEvent?.startDate);
        let formattedDate = dateFormatter.formatDate(localDate);
        formattedDate += ' (' + dateFormatter.formatHoursAndSeconds(localDate) + ')';

        return formattedDate;
    })

    onMounted(() => {
        fetchEventList(projectStore.selectedProject.id, '').then((fetchedEvents) => {
            events.value = fetchedEvents;
        });
    });

    const resetEvent = () => {
        props.pageSection.calendarEvent.calendarEvent = null; // only resets it in the local state
    };

    const selectEvent = (event) => {
        props.pageSection.calendarEvent.calendarEvent = event;
        props.onPageSectionSubmit({
            calendarEvent: {
                calendarEvent: event.id,
            },
        });
    };
</script>