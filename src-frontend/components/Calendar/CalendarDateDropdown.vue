<template>
    <VDropdown>
        <button class="btn m-0 p-0">
            <p class="m-0">{{ months[month] }} {{ year }}</p>
        </button>

        <template #popper>
            <div class="p-2 d-flex flex-row justify-content-between gap-2" style="min-width: 15rem">
                <select
                    class="form-control"
                    @change="emit('update', { month: parseInt($event.target.value), year })"
                >
                    <option
                        v-for="(monthOption, index) in months"
                        :value="index"
                        :selected="index === month"
                    >{{ monthOption }}</option>
                </select>
                <input
                    type="number"
                    class="form-control"
                    :value="year"
                    :min="year - 20"
                    :max="year + 20"
                    @change="emit('update', { month, year: parseInt($event.target.value) })"
                />
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref } from 'vue';
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    const emit = defineEmits(['update']);
    const props = defineProps({
        month: {
            type: Number,
            required: true,
        },
        year: {
            type: Number,
            required: true,
        },
    });
</script>