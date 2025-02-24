<template>
    <div class="row m-0 p-0">
        <div class="mt-5 col-sm-4 col-lg-2 p-0 m-0">
            <ul class="nav nav-pills nav-fill d-flex flex-column gap-5">
                <li class="nav-item">
                    <router-link
                        :to="{ name: 'Settings' }"
                        class="nav-link active d-flex flex-row gap-3 align-items-center"
                    >
                        <font-awesome-icon :icon="['fas', 'cog']" />
                        Personal details
                    </router-link>
                </li>
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
        <div class="col-sm-8 col-lg-9 m-0 p-0 ps-5 d-flex flex-column">
            <div class="card section-card section-card-small">
                <div class="card-body d-flex flex-column align-items-center gap-2">
                    <h5><span class="text-muted">Logged in as </span><span class="black bold">{{ userStore.currentUser.email }}</span></h5>
                    <div
                        v-if="hasProfilePicture"
                        class="profile-picture-container"
                        :style="{ backgroundImage: 'url(' + userStore.currentUser.profilePicture.publicFilePath + ')' }"
                        @click="openFileExplorerForUpload"
                    ></div>

                    <button class="btn btn-dark" @click="openFileExplorerForUpload">
                        {{ hasProfilePicture ? 'Update' : 'Upload' }} profile picture
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { computed, ref, onMounted } from 'vue';
    import { useUserStore } from '@/stores/UserStore.js';

    const userStore = useUserStore();
    
    const hasProfilePicture = computed(() => {
        return userStore.currentUser.profilePicture != null;
    })

    const openFileExplorerForUpload = () => {
        let input = document.createElement('input');
        input.multiple = false;
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = () => {
            let files = input.files?? null;

            if (files.length !== 1) {
                console.error('Only one file can be uploaded at a time');
                return;
            }

            userStore.uploadUserProfilePicture(files[0]);
        };
        input.click();
    };
</script>