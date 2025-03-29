<template>
    <VDropdown
        v-model:shown="isDropdownVisible"
        class="d-flex"
    >
        <button
            class="flex-fill btn m-0 p-2 d-flex flex-row align-items-center gap-2"
        >
            <span class="btn btn-lg m-0 p-1 btn-dark-gray" v-tooltip="'This calendar entry is related to an event.'">
                <font-awesome-icon :icon="['fas', 'calendar-day']" />
            </span>
            <div>
                <p class="m-0">{{ calendarEvent.name }}</p>
                <div class="d-flex flex-row align-items-center gap-1">
                    <TagBadge 
                        v-for="eventTag in calendarEvent.eventTags"
                        :key="eventTag.id"
                        :tag="eventTag.tag"
                    />
                </div>
            </div>
        </button>

        <template #popper>
            <div class="p-2" style="min-width: 20rem">
                <!-- if the tag menu is not open, general tag controls are displayed here -->
                <div v-if="!isInTaskContext" class="d-flex flex-column gap-3"> 
                    <div class="d-flex flex-column gap-1">
                        <label for="eventName" class="text-muted">Event name</label>
                        <input
                            type="text"
                            name="eventName"
                            class="form-control"
                            placeholder="e.g. Meeting with team"
                            v-model="calendarEvent.name"
                            @keyup="debouncedEventUpdate"
                            @keyup.enter="submitEvent"
                        />

                        <div class="d-flex flex-row gap-2 align-items-center">
                            <div>
                                <label class="m-0 p-0 text-muted" for="eventDate">Event date</label>
                                <input
                                    name="eventDate"
                                    type="date"
                                    class="form-control m-0"
                                    v-model="calendarEventStartDate"
                                    @change="updateEventDate"
                                />
                            </div>
                            <div>
                                <label class="m-0 p-0 text-muted" for="eventTime">Event time</label>
                                <input
                                    name="eventTime"
                                    type="time"
                                    class="form-control m-0"
                                    v-model="calendarEventStartTime"
                                    @change="updateEventDate"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-row justify-content-between align-items-center gap-3">
                        <div class="d-flex flex-row gap-2 align-items-center">
                            <button class="btn btn-dark-gray m-0 p-2" v-tooltip="'Add tags to event'" @click="isInTaskContext = !isInTaskContext"> 
                                <font-awesome-icon :icon="['fa', 'tag']" />
                            </button>

                            <button
                                class="btn btn-sm btn-tag"
                                v-for="eventTag in calendarEvent.eventTags"
                                :key="eventTag.id"
                                v-tooltip="eventTag.tag.name"
                                :style="{'background-color': eventTag.tag.color}"
                                @click="isInTaskContext = !isInTaskContext"
                            >
                                &nbsp;&nbsp;&nbsp;
                            </button>
                        </div>

                        <button class="btn btn-dark-gray" @click="deleteEvent">
                            <font-awesome-icon :icon="['fa', 'trash']" />
                        </button>
                    </div>

                    <div class="d-flex flex-column gap-2" v-if="calendarEvent.pageSectionCalendarEvents.length > 0">
                        <hr>
                        <p class="m-0">Mentioned in</p>

                        <ul class="nav nav-pills nav-fill w-100">
                            <li class="nav-item" v-for="pageSectionCalendarEvent in calendarEvent.pageSectionCalendarEvents">
                                <router-link
                                    :to="{ name: 'WikiPage', params: {id: pageSectionCalendarEvent.pageSection.pageTab.page.id} }"
                                    class="nav-link inactive"
                                    :href="'#'+pageSectionCalendarEvent.pageSection.pageTab.page.id"
                                >
                                    {{ pageSectionCalendarEvent.pageSection.pageTab.page.name }}
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </div>
                <CalendarEventTagDropdown
                    v-else
                    :tags="eventProjectTags"
                    :eventName="calendarEvent.name"
                    @update="tags = $event"
                    @back="isInTaskContext = !isInTaskContext"
                />
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { computed, nextTick, onMounted, watch, ref, useTemplateRef } from 'vue';
    import { useDebounceFn } from '@vueuse/core';
    import CalendarEventTagDropdown from '@/components/Calendar/Event/CalendarEventTagDropdown.vue';
    import TagBadge from '@/components/Tag/TagBadge.vue';
    import { useCalendarStore } from '@/stores/CalendarStore';

    const calendarStore = useCalendarStore();
    const nowDate = new Date().toISOString().split('T')[0];
    const nowMonth = new Date().getMonth();
    const emit = defineEmits(['update']);
    const props = defineProps({
        event: {
            type: Object,
            required: true,
        },
    });
    const calendarEvent = ref (props.event);
    const calendarEventStartDate = ref(props.event.startDate.split('T')[0]); // load into own ref to reformat.
    const calendarEventStartTime = ref(props.event.startDate.split('T')[1].split('+')[0]); // load into own ref to reformat.

    const isDropdownVisible = ref(false);
    const isInTaskContext = ref(false);

    const canSubmitEvent = computed(() => {
        return calendarEvent.value.name.trim() !== '';
    });

    const eventProjectTags = computed(() => calendarEvent.value.eventTags.map((eventTag) => eventTag.tag));

    const updateEventDate = (event) => {
        calendarEvent.value.startDate = `${calendarEventStartDate.value}T${calendarEventStartTime.value}`;
        debouncedEventUpdate();
    }

    const updateEvent = () => {
        if (!canSubmitEvent.value) {
            return;
        }

        calendarStore.updateEvent(calendarEvent.value);
    };

    const debouncedEventUpdate = useDebounceFn(async () => {
        await calendarStore.updateEvent(calendarEvent.value);
    }, 300);

    const deleteEvent = () => {
        isDropdownVisible.value = false;
        calendarStore.deleteEvent(calendarEvent.value);
    };
</script>