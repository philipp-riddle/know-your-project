<template>
    <div
        class="card project-card"
        :class="{'active': projectStore.selectedProject?.id === project.id}"
        :key="project.id"
    >
        <div class="card-body d-flex flex-column gap-2 justify-content-end">
            <p class="m-0">{{ project.name }}</p>

            <div class="d-flex flex-row gap-3 justify-content-end">
                <button class="btn btn-sm btn-outline-danger" @click="deleteInvitation">
                    <font-awesome-icon :icon="['fas', 'times']" />
                </button>

                <button class="btn btn-sm btn-outline-success" @click="acceptInvitation">
                    <font-awesome-icon :icon="['fas', 'check']" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import DeletionButton from '@/components/Util/DeletionButton.vue';

    const emit = defineEmits(['accept', 'delete']);
    const props = defineProps({
        userInvitation: {
            type: Object,
            required: true,
        }
    });
    const project = ref(props.userInvitation.project);

    const userStore = useUserStore();
    const projectStore = useProjectStore();

    const projectIdDeleteDropdownVisible = ref(false);

    const acceptInvitation = async () => {
        await userStore.acceptUserProjectInvitation(props.userInvitation).then((createdProjectUser) => {
            createdProjectUser.project = project.value; // enrich object with project object
            userStore.currentUser.projectUsers.push(createdProjectUser);
            emit('accept', props.userInvitation);
        });
    };

    const deleteInvitation = async () => {
        await userStore.deleteUserProjectInvitation(props.userInvitation).then(() => {
            emit('delete', props.userInvitation);
        });
    };
</script>