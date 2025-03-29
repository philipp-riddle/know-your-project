<template>
    <div class="d-flex flex-column" v-if="calendarRows.length > 0">
        <div class="row mb-3">
            <div class="col-sm-6 offset-md-4 col-md-4 d-flex flex-row justify-content-center align-items-center">
                <ul class="nav calendar-nav nav-pills d-flex flex-row justify-content-center align-items-center gap-3">
                    <li class="nav-item">
                        <button class="nav-link inactive" @click="goMonthBack">
                            <font-awesome-icon :icon="['fas', 'chevron-left']" />
                        </button>
                    </li>
                    <li class="nav-item">
                        <CalendarDateDropdown
                            :month="currentMonth"
                            :year="currentYear"
                            @update="updateDateValues"
                        />
                    </li>
                    <li class="nav-item">
                        <button class="nav-link inactive" @click="goMonthForward">
                            <font-awesome-icon :icon="['fas', 'chevron-right']" />
                        </button>
                    </li>
                </ul>
            </div>

            <div class="col-sm-6 col-md-4 d-flex flex-row justify-content-end align-items-center">
                <CalendarCreateEventDropdown />
            </div>
        </div>

        <div class="calendar">
            <div class="row m-0 p-0">
                <div
                    class="card weekday-card col m-0 p-0"
                    v-for="day in weekDays"
                >
                    <div class="card-body m-0 p-0 d-flex flex-row justify-content-end align-items-center">
                        <p class="m-0">{{ day }}</p>
                    </div>
                </div>
            </div>
            <div
                v-for="week in calendarRows"
                class="row m-0 p-0"
            >
                <div
                    v-for="date in week"
                    :key="startDay+'-'+date.date"
                    class="card calendar-card col m-0 p-0"
                >
                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-end align-items-center">
                            <!-- <h5
                                :class="{
                                    'text-muted': date.month !== currentMonth,
                                    'current-day': date.day === nowDay && date.month === nowMonth && date.year === nowYear,
                                }"
                            >{{ date.day }}</h5> -->
                            <CalendarCreateEventDropdown
                                :date="date.date"
                            />
                        </div>

                        <div
                            v-for="eventType in Object.keys(date.events)"
                            class="d-flex flex-column gap-2"
                        >
                            <div
                                v-for="event in date.events[eventType]"
                                class="calendar-event"
                            >
                                <CalendarManageTask
                                    v-if="eventType == 'tasks'"
                                    :event="event"
                                />
                                <CalendarManageEventDropdown
                                    v-else-if="eventType == 'events'"
                                    :event="event"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- nested router view to open pages, tasks, sessions etc in a modal -->
        <router-view></router-view>
    </div>
</template>
<script setup>
    import PageExplorer from '@/components/Page/Explorer/PageExplorer.vue';
    import CalendarDateDropdown from '@/components/Calendar/CalendarDateDropdown.vue';
    import CalendarCreateEventDropdown from '@/components/Calendar/Event/CalendarCreateEventDropdown.vue';
    import CalendarManageEventDropdown from '@/components/Calendar/Event/CalendarManageEventDropdown.vue';
    import CalendarManageTask from '@/components/Calendar/CalendarManageTask.vue';
    import { useDateConverter } from '@/composables/DateConverter';
    import { fetchProjectEvents } from '@/stores/fetch/CalendarFetcher';
    import { useCalendarStore } from '@/stores/CalendarStore';
    import { useProjectStore } from '@/stores/ProjectStore';
    import { useTaskStore } from '@/stores/TaskStore';
    import { ref, onMounted, watch } from 'vue';
    import { useDebounceFn } from '@vueuse/core';

    const calendarStore = useCalendarStore();
    const projectStore = useProjectStore();
    const taskStore = useTaskStore();
    const dateConverter = useDateConverter();
    const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    // variables for the current date.
    // non-refs on purpose to avoid reactivity.
    const nowMonth = new Date().getMonth();
    const nowYear = new Date().getFullYear();
    const nowDay = new Date().getDate();

    // 6 * 7 days
    const weeksToInclude = 6;
    const daysToInclude = 7;

    // variables for the current date the calendar is displaying.
    // refs for reactivity.
    const currentMonth = ref(null);
    const currentYear = ref(null);
    const currentDay = ref(null);
    const startDay = ref(null); // the day the calendar starts on. can be in the last month.
    const endDay = ref(null); // the day the calendar ends on. can be in the next month.

    /**
     * The calendar is a 6x7 grid, where each row is a week and each column is a day.
     * Each day can consist of a date and associated task deadlines, meetings, etc.
     */
    const calendarRows = ref([]);

    onMounted(() => {
        // set the current date ONCE when the component is mounted;
        // afterwards the user is responsible for changing the date.
        currentMonth.value = new Date().getMonth();
        currentYear.value = new Date().getFullYear();
        currentDay.value = new Date().getDate();
    
        reloadDateValues();
    });

    // whenever the tasks change (e.g. a new task is added or due date is changed), reload the calendar rows.
    watch (() => taskStore.tasks, async (newTasks) => {
        await calendarStore.getEventHashmap(startDay.value, endDay.value);
        reloadCalendarRows();
    });

    // whenever the global calendar event hashmap changes, reload the calendar rows.
    watch (() => calendarStore.eventHashmap, () => {
        debouncedReloadCalendarRows();
    });

    const goMonthBack = () => {
        currentYear.value = currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value;
        currentMonth.value = currentMonth.value === 0 ? 11 : currentMonth.value - 1;
        reloadDateValues();
    }

    const goMonthForward = () => {
        currentYear.value = currentMonth.value === 11 ? currentYear.value + 1 : currentYear.value;
        currentMonth.value = currentMonth.value === 11 ? 0 : currentMonth.value + 1;
        reloadDateValues();
    }

    const updateDateValues = (event) => {
        currentMonth.value = event.month;
        currentYear.value = event.year;
        reloadDateValues();
    }

    const reloadDateValues = async () => {
        const firstDayOfMonth = new Date(currentYear.value, currentMonth.value, 1);
        const weekdayOfFirstDay = firstDayOfMonth.getDay();

        const previousMonthYear = currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value;
        const previousMonth = new Date(previousMonthYear, currentMonth.value === 0 ? 11 : currentMonth.value - 1, 1);
        const daysOfPreviousMonth = new Date(previousMonthYear, currentMonth.value === 0 ? 11 : currentMonth.value - 1, 0).getDate();
        startDay.value = new Date(previousMonthYear, previousMonth.getMonth(), daysOfPreviousMonth - weekdayOfFirstDay + 2);
        endDay.value = new Date(currentYear.value, startDay.value.getMonth(), startDay.value.getDate() + weeksToInclude * daysToInclude);

        await calendarStore.getEventHashmap(startDay.value, endDay.value); // updating the event hashmap automatically updates the calendar rows
    };

    /**
     * The calendar rows can be reloaded separetely from the date values, e.g. to account for new events but keep the current date.
     */
    const reloadCalendarRows = async () => {
        calendarRows.value = []; // reset the calendar rows
        let currentDayIterator = new Date(startDay.value); // this clones the date object instead of changing the ref

        for (let week = 0; week < weeksToInclude; week++) {
            let week = [];

            for (let day = 0; day < daysToInclude; day++) {
                // date formatted as YYYY-MM-DD
                const formattedDate = currentDayIterator.getFullYear() + '-' + (currentDayIterator.getMonth() + 1).toString().padStart(2, '0') + '-' + currentDayIterator.getDate().toString().padStart(2, '0');

                week.push({
                    date: formattedDate,
                    day: currentDayIterator.getDate(),
                    month: currentDayIterator.getMonth(),
                    year: currentDayIterator.getFullYear(),
                    events: calendarStore.eventHashmap[formattedDate] ?? {},
                });
                currentDayIterator.setDate(currentDayIterator.getDate() + 1);
            }

            calendarRows.value.push(week);
        }
    };
    const debouncedReloadCalendarRows = useDebounceFn(reloadCalendarRows, 100); // debounce the reload function to avoid multiple calls in quick succession; e.g. when rebuilding the hashmap
</script>