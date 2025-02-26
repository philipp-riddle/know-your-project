<template>
    <div class="card">
        <div class="card-body d-flex flex-column align-items-center justify-content-between gap-3">
            <div
                v-if="hasProfilePicture"
                class="profile-picture-container"
                :style="{ backgroundImage: 'url(' + user.profilePicture.publicFilePath + ')' }"
                @click="openFileExplorerForUpload"
            ></div>
            <button v-else class="btn btn-sm btn-dark" @click="openFileExplorerForUpload">
                Upload profile picture
            </button>

            <div class="d-flex flex-column align-items-center">
                <p class="m-0 text-muted">Logged in as</p>
                <h5 class="m-0 black bold">{{ user.email }}</h5>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import { useUserStore } from '@/stores/UserStore.js';

    const props = defineProps({
        user: {
            type: Object,
            required: true
        },
    });
    const userStore = useUserStore();

    const hasProfilePicture = computed(() => {
        return props.user.profilePicture != null;
    });

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