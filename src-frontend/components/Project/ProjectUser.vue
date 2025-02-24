<template>
    <div class="card">
        <div class="card-body d-flex flex-row justify-content-between align-items-center">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div
                    v-if="projectUser.user.profilePicture != null"
                    class="profile-picture-container profile-picture-container-small"
                    :style="{ backgroundImage: 'url(' + projectUser.user.profilePicture.publicFilePath + ')' }"
                ></div>

                <div>
                    <h5 class="m-0">
                        {{ projectUser.user.email }}
                        <span v-if="isCurrentUser">(you)</span>
                    </h5>

                    <div class="d-flex flex-row gap-2 align-items-center">
                        <p class="text-muted m-0 d-flex flex-row gap-3">
                            <div v-if="project.owner.id == projectUser.user.id">
                                Owner
                            </div>
                            <div v-else>
                                Member
                            </div>
                        </p>
                        <ProjectUserTagControl
                            :projectUser="projectUser"
                            :project="project"
                            @updateProjectUser="(projectUser) => $emit('updateProjectUser', projectUser)"
                        />
                    </div>
                </div>
            </div>

            <div class="d-flex flex-row gap-2 align-items-center">
                <button v-if="!isCurrentUser && isCurrentUserOwner" class="btn btn-sm m-0" @click="onDeleteProjectUser" v-tooltip="'Remove user from project'">
                    <font-awesome-icon :icon="['fas', 'user-minus']" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import ProjectUserTagControl from '@/components/Project/ProjectUserTagControl.vue';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const userStore = useUserStore();
    const projectStore = useProjectStore();

    const emit = defineEmits(['updateProjectUser', 'deleteProjectUser']);
    const props = defineProps({
        projectUser: {
            type: Object,
            required: true,
        },
        project: {
            type: Object,
            required: true,
        },
    });

    const isCurrentUser = computed(() => {
        return userStore.currentUser.id == props.projectUser.user.id;
    });

    const isCurrentUserOwner = computed(() => {
        return props.project.owner.id == userStore.currentUser.id;
    });

    const onDeleteProjectUser = () => {
        projectStore.deleteProjectUser(props.projectUser.id).then(() => {
            emit('deleteProjectUser', props.projectUser);
        });
    };
</script>