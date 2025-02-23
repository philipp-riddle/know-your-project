<template>
    <VDropdown
        v-model:shown="isDropdownVisible"
    >
        <button
            class="btn"
            :class="{
                'btn-dark': !hasProvidedDate, // if date is not given, it's a create event button
            }"
        >
            <h5
                v-if="hasProvidedDate"
                :class="{
                    'text-muted': date !== nowDate && date.month !== nowMonth,
                    'current-day': date === nowDate,
                }"
            >{{ dateDay }}</h5>
            <p v-else class="m-0">Create event</p>
        </button>

        <template #popper>
            <div class="p-2" style="min-width: 20rem">
                <!-- if the tag menu is not open, general tag controls are displayed here -->
                <div v-if="!isInTaskContext" class="d-flex flex-column gap-3"> 
                    <div class="d-flex flex-column gap-1">
                        <label v-if="!hasProvidedDate" class="m-0 p-0 text-muted" for="eventDate">Event date</label>
                        <input
                            v-if="!hasProvidedDate"
                            name="eventDate"
                            type="date"
                            class="form-control m-0"
                            v-model="date"
                        />

                        <label for="eventName" class="text-muted">Event name</label>
                        <input
                            type="text"
                            name="eventName"
                            class="form-control"
                            placeholder="e.g. Meeting with team"
                            v-model="eventName"
                            @keyup.enter="submitEvent"
                        />
                    </div>

                    <div class="d-flex flex-row justify-content-between align-items-center gap-3">
                        <div class="d-flex flex-row gap-2 align-items-center">
                            <button class="btn btn-dark-gray m-0 p-2" v-tooltip="'Add tags to event'" @click="isInTaskContext = !isInTaskContext"> 
                                <font-awesome-icon :icon="['fa', 'tag']" />
                            </button>

                            <button
                                class="btn btn-sm btn-tag"
                                v-for="tag in tags"
                                :key="tag.id"
                                v-tooltip="tag.name"
                                :style="{'background-color': tag.color}"
                                @click="isInTaskContext = !isInTaskContext"
                            >
                                &nbsp;&nbsp;&nbsp;
                            </button>
                        </div>
                        <button
                            class="btn btn-primary m-0 p-2"
                            :disabled="!canSubmitEvent"
                            @click="submitEvent"
                        >
                            <font-awesome-icon :icon="['fa', 'check']" />
                        </button>
                    </div>
                </div>
                <CalendarEventTagDropdown
                    v-else
                    :tags="tags"
                    :eventName="eventName"
                    @update="tags = $event"
                    @back="isInTaskContext = !isInTaskContext"
                />
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { computed, onMounted, ref } from 'vue';
    import CalendarEventTagDropdown from '@/components/Calendar/Event/CalendarEventTagDropdown.vue';
    import { useCalendarStore } from '@/stores/CalendarStore';

    const calendarStore = useCalendarStore();
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    const nowDate = new Date().toISOString().split('T')[0];
    const nowMonth = new Date().getMonth();
    const emit = defineEmits(['update']);
    const props = defineProps({
        date: {
            type: String,
            required: false,
        },
    });
    const hasProvidedDate = props.date !== undefined;
    const date = ref(props.date ?? nowDate);
    const dateDay = ref(new Date(date.value).getDate());
    const isDropdownVisible = ref(false);
    const eventName = ref('New event'); // set default event name; this value is modelled by an input field
    const isInTaskContext = ref(false);

    /**
     * These tags are the tags that are currently assigned to the new event.
     * They will be submitted when the user confirms the event creation by clicking the submit button.
     */
    const tags = ref([]);

    const canSubmitEvent = computed(() => {
        return eventName.value.trim() !== '';
    });

    const submitEvent = () => {
        if (!canSubmitEvent.value) {
            return;
        }

        // for now the end date is always null
        calendarStore.createEvent(eventName.value, new Date(date.value), null, tags.value.map((tag) => tag.id)).then(() => {
            isDropdownVisible.value = false;
        });
    };
</script>