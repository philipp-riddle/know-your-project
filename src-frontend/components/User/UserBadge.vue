<template>
    <div class="d-flex flex-row align-items-center gap-3">
        <div
            v-if="user.profilePicture != null"
            class="profile-picture-container"
            :class="imageSizeClass"
            :style="{ backgroundImage: 'url(' + user.profilePicture.publicFilePath + ')' }"
            v-tooltip="user.email"
        >
            &nbsp;&nbsp;&nbsp;
        </div>
        <div
            v-else
            class="profile-picture-container"
            :class="imageSizeClass"
            v-tooltip="user.email"
        >
            <font-awesome-icon :icon="['fas', 'user']" />
        </div>
        <div v-if="!minimal">
            <p class="m-0">{{ user.email }}</p>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        user: {
            type: Object,
            required: true,
        },
        minimal: { // if the badge is set to minimal only the profile picture is shown
            type: Boolean,
            required: false,
            default: true,
        },
        imageSize: {
            type: String,
            required: false,
            default: 'xs',
        }
    });

    const imageSizeClass = computed(() => {
        return 'profile-picture-container-'+props.imageSize;
    });
</script>