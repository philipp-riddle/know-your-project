<template>
    <VDropdown
        :distance="6"
        v-model:shown="showPopover"
        :triggers="[]"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <button
            v-tooltip="tooltip"
            class="btn btn-sm"
            @click="() => showPopover = !showPopover"
            :class="{
                'btn-dark-gray': darkMode, // in dark mode we only have one color; this ensures it can be seen

                'btn-light-gray': !darkMode && !showPopover, // this makes it appear 'unselected'
                'btn-dark-gray': !darkMode && showPopover, // this makes it appear 'selected'
            }"
        >
            <font-awesome-icon :icon="['fas', 'trash']" />
        </button>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="p-3">
                <h5>Are you sure?</h5>
                <p>This {{ label }} will be deleted forever.</p>

                <div class="d-flex flex-row justify-content-end gap-3">
                    <button class="btn btn-sm btn-danger" @click.stop="onClickYes">Yes</button>
                    <button class="btn btn-sm btn-secondary" @click.stop="onClickCancel">Cancel</button>
                </div>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref, computed, watch } from 'vue';

    const emit = defineEmits(['onShowDropdown', 'onHideDropdown', 'onConfirm'])
    const props = defineProps({
        label: {
            type: String,
            required: false,
            default: 'item',
        },
        showTooltip: {
            type: Boolean,
            required: false,
            default: true,
        },
        darkMode: {
            type: Boolean,
            required: false,
            default: false,
        },
    });
    const showPopover = ref(false);
    const tooltip = computed(() => {
        return props.showTooltip ? 'Delete ' + props.label : '';
    });

    watch(() => showPopover.value, (newValue) => {
        if (newValue) {
            emit('onShowDropdown');
        } else {
            emit('onHideDropdown');
        }
    });

    const onClickYes = () => {
        emit('onConfirm');
        showPopover.value = false;
    };

    const onClickCancel = () => {
        showPopover.value = false;
    };
</script>