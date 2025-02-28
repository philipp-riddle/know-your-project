<template>
    <div class="d-flex flex-row justify-content-center align-items-center">
        <CreateProjectDropdown />
    </div>

    <div class="row m-0 p-0">
        <div class="col-sm-4 col-lg-2 p-0 m-0 d-flex flex-column gap-3">
            <LoggedInUserBadge :user="userStore.currentUser" />

            <ul class="nav nav-pills nav-fill d-flex flex-column">
                <li class="nav-item">
                    <a
                        href="/logout"
                        v-tooltip="'Logout'"
                        class="nav-link inactive d-flex flex-row gap-3 align-items-center"
                    >
                        <font-awesome-icon :icon="['fas', 'right-from-bracket']" />
                        Logout
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-sm-8 col-lg-9 m-0 p-0 ps-5 d-flex flex-column gap-5">
            <div class="d-flex flex-column gap-3">
                <h2>Your projects</h2>

                <div class="d-flex flex-row gap-2">
                    <ProjectCard
                        v-for="projectUser in userStore.currentUser.projectUsers"
                        :project="projectUser.project"
                        :projectUser="projectUser"
                        :key="projectUser.id"
                    />
                </div>
            </div>
            <div class="d-flex flex-column gap-3" v-if="userInvitations && userInvitations.length > 0">
                <h2>Your project invitations</h2>

                <div class="d-flex flex-row gap-2">
                    <!-- the title seems paradox but the user invitation is a project invitation for a certain user -->
                    <ProjectInvitationCard
                        v-for="userInvitation in userInvitations"
                        :userInvitation="userInvitation"
                        :key="userInvitation.id"
                        @accept="removeUserInvitation"
                        @delete="removeUserInvitation"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { computed, ref, onMounted } from 'vue';
    import CreateProjectDropdown from '@/components/Project/CreateProjectDropdown.vue';
    import ProjectCard from '@/components/Project/ProjectCard.vue';
    import ProjectInvitationCard from '@/components/Project/ProjectInvitationCard.vue';
    import LoggedInUserBadge from '@/components/User/LoggedInUserBadge.vue';
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const userStore = useUserStore();
    const projectStore = useProjectStore();

    const projectIdDeleteDropdownVisible = ref(null);
    const userInvitations = ref([]);
    
    const hasProfilePicture = computed(() => {
        return userStore.currentUser.profilePicture != null;
    });

    const allProjects = computed(() => {
        return userStore.currentUser.projectUsers
            .map((projectUser) => projectUser.project); // map the projectUser objects to the project objects as we only need the project objects
    });

    const removeUserInvitation = (userInvitation) => {
        userInvitations.value = userInvitations.value.filter((invitation) => invitation.id !== userInvitation.id);
    };

    onMounted(() => {
        userStore.getUserInvitations().then((invitations) => {
            userInvitations.value = invitations;
        });
    });
</script>