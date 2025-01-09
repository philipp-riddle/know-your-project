<template>
    <VDropdown
        :distance="6"
        :shown="showPopover"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <button
            v-tooltip="tooltip"
            class="btn btn-sm btn-danger"
        >
            <font-awesome-icon :icon="['fas', 'trash']" />
        </button>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="p-3">
                <h5>Are you sure?</h5>
                <p>This {{ label }} will be deleted forever.</p>

                <div class="d-flex flex-row justify-content-end gap-3">
                    <button class="btn btn-sm btn-danger" @click.stop="onConfirm(yes)">Yes</button>
                    <button class="btn btn-sm btn-secondary" @click.stop="showPopover = false">No</button>
                </div>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref, computed } from 'vue';

    const props = defineProps({
        onConfirm: {
            type: Function,
            required: true,
        },
        label: {
            type: String,
            required: false,
            default: 'item',
        },
    });
    const showPopover = ref(false);
    const tooltip = computed(() => 'Delete ' + props.label);
</script>