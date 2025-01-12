<template>
    <div
        v-if="threadStore.selectedThread"
        class=" thread-box card"
    >
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <p class="m-0 bold">THREAD</p>
                <button
                    v-tooltip="'Close thread'"
                    class="btn btn-sm m-0 p-0"
                    @click="threadStore.selectedThread = null">
                    <font-awesome-icon :icon="['fas', 'times']" />
                </button>
            </div>
        </div>
        <div class="card-body thread-messages">
            <div class="d-flex flex-column gap-2">
                <ThreadItem
                    v-for="threadItem in threadStore.selectedThread.threadItems"
                    :key="threadItem.id"
                    :threadItem="threadItem"
                />
            </div>
        </div>
        <div class="card-footer p-3 pb-4 d-flex flex-row gap-3 align-items-center justify-content-between">
            <TextEditor
                :text="currentText"
                @onChange="currentText = $event"
                :focus="!threadStore.isCreatingThread"
                placeholder="e.g. I liked X, but I think Y could be improved."
                :disabled="threadStore.isCreatingThread"
            />
            <button
                class="btn btn-sm btn-dark"
                @click="sendThreadMessage"
                :disabled="!canSend"
                v-tooltip="canSend ? 'Send' : ''"
            >
                <div v-if="isCreatingItem" class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <font-awesome-icon v-else :icon="['fas', 'paper-plane']" />
            </button>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import { useThreadStore } from '@/stores/ThreadStore.js';
    import TextEditor from '@/components/Util/TextEditor.vue';
    import ThreadItem from '@/components/Thread/ThreadItem.vue';

    const threadStore = useThreadStore();
    const currentText = ref('');
    const isCreatingItem = ref(false);

    const canSend = computed(() => {
        return !isCreatingItem.value && currentText.value.trim() !== '' && currentText.value.trim() !== '<p></p>';
    });

    const sendThreadMessage = () => {
        if (!canSend.value || isCreatingItem.value) {
            return;
        }

        isCreatingItem.value = true;
        threadStore.createThreadPromptItem(threadStore.selectedThread.id, currentText.value).then(() => {
            currentText.value = '';
            isCreatingItem.value = false;
        });
    };
</script>