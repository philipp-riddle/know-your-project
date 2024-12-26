<template>
    <div class="d-flex flex-row align-items-center gap-3">
        <input
            v-if="displayCompleteInput"
            class="form-check-input m-0"
            type="checkbox"
            v-model="item.complete"
            v-tooltip="item.complete ? 'Mark item as incomplete' : 'Mark item as complete'"
            @click="onCheckboxClick"
        />
        <input
            class="form-control magic-input"
            type="text"
            v-model="item.name"
            ref="createItemInputField"
            :placeholder="placeholder"
            @keyup="onTitleKeyup"
            @keyup.enter="onTitleEnterClick"
        />
    </div>
</template>

<script setup>
    import { ref, onMounted, computed } from 'vue';
    import { useDebounceFn } from '@vueuse/core'

    const props = defineProps({
        onItemEnter: {
            type: Function,
            required: false,
        },
        onItemUpdate: {
            type: Function,
            required: false,
        },
        resetOnUpdate: {
            type: Boolean,
            required: false,
            default: false,
        },
        item: {
            type: Object,
            required: false,
        },
        focusOnInit: {
            type: Boolean,
            required: false,
            default: true,
        },
        displayCompleteInput: {
            type: Boolean,
            required: false,
            default: true,
        },
    });

    const createItemInputField = ref(null);
    // if no item is provided, create a new one
    const item = ref(props.item ?? {
        name: '',
        complete: false,
    });
    const placeholder = computed(() => {
        return props.item ? 'Type checklist item...' : 'Type new checklist item...';
    });
    const debouncedChecklistItemUpdate = useDebounceFn((item) => {
        props.onItemUpdate(item);
    }, 1000);

    onMounted (() => {
        if (props.focusOnInit) {
            createItemInputField.value.focus();
        }
    });

    const onTitleKeyup = () => {
        if (createItemInputField.value.value.trim(' ') === '') {
            createItemInputField.value.value = '';
        }

        if (props.item?.id && createItemInputField.value.value.trim() !== '') {
            item.value.name = createItemInputField.value.value;
            onChecklistItemUpdate(item.value);
        }
    };

    const onCheckboxClick = () => {
        item.value.complete = !item.value.complete;

        onChecklistItemUpdate(item.value);
    };

    const onChecklistItemUpdate = async () => {
        if (props.onItemUpdate) {
            await debouncedChecklistItemUpdate(item.value);
        }

        if (props.resetOnUpdate) {
            item.value.name = ''; // @todo use default object from somewhere else
            item.value.complete = false;
        }
    };

    const onTitleEnterClick = () => {
        if (createItemInputField.value.value.trim(' ') === '') {
            return;
        }

        item.value.name = createItemInputField.value.value;

        if (props.onItemEnter) {
            props.onItemEnter(item.value);
        }

        onChecklistItemUpdate(item.value);
    };
</script>

<style scoped lang="sass">
	@import '@/styles/colors.scss';

    input.form-check-input {
        border: 5px solid $green;
        border-radius: 2rem;
        padding:3%;
        background-color: 5px solid $green !important;

        &:focus {
            box-shadow: none !important;
            background-color: 5px solid $green;
        }

        &:checked, &:hover {
            background-color: $green !important;
            cursor: pointer;
        }
    }
</style>