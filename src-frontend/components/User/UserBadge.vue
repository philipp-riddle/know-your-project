<template>
    <div class="d-flex flex-row align-items-center gap-3">
        <div
            v-if="user.profilePicture != null"
            class="profile-picture-container"
            :class="imageSizeClass"
            :style="imageStyles"
            v-tooltip="user.email"
        >
            &nbsp;&nbsp;&nbsp;
        </div>
        <div
            v-else
            class="profile-picture-container d-flex flex-row align-items-center justify-content-center"
            :class="imageSizeClass"
            v-tooltip="user.email"
        >
            <font-awesome-icon :icon="['fas', 'user']" />
        </div>
        <div v-if="!minimal">
            <p class="m-0">{{ user.email }}</p>
            <div class="d-flex flex-row gap-2">
                <TagBadge
                    v-for="projectUserTag in projectUserTags"
                    :key="projectUserTag.id"
                    :tag="projectUserTag.tag"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
    import TagBadge from '@/components/Tag/TagBadge.vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';
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
        },
        borderColor: {
            type: String,
            required: false,
        }
    });
    const projectStore = useProjectStore();

    const imageSizeClass = computed(() => {
        return 'profile-picture-container-'+props.imageSize;
    });

    const imageStyles = computed(() => {
        const styles = {};

        if (props.user.profilePicture != null) {
            styles.backgroundImage = 'url(' + props.user.profilePicture.publicFilePath + ')';
        }

        if (props.borderColor) {
            styles.borderColor = props.borderColor + ' !important';
        }

        return styles;
    });

    const projectUserTags = computed(() => {
        return projectStore.selectedProject.projectUsers.find((projectUser) => {
            return projectUser.user.id === props.user.id;
        })?.tags ?? [];
    })
</script>