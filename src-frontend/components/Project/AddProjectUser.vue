<template>
    <VDropdown
        :distance="6"
        :shown="showPopover"
        class="d-flex flex-row align-items-center gap-3"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <button class="btn btn-lg p-0 m-0" v-tooltip="'Invite a user to your project'">
            <strong class="m-0 p-0">+</strong>
        </button>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="m-4">
                <div class="d-flex flex-row align-items-end gap-4">
                    <div class="col-sm-10">
                        <h5>Invite a user by their email to your project</h5>
                        <input
                            type="email"
                            class="form-control"
                            v-model="addProjectUserEmail"
                            placeholder="e.g. chris@company.io"
                            @keyup.enter="onSubmitEmail()"
                            @keyup="invitedHint = null"
                        />
                    </div>
                    <div class="col-sm-2">
                        <button
                            class="btn btn-primary"
                            :disabled="!hasValidEmail"
                            @click="onSubmitEmail()"
                        >
                            Invite
                        </button>
                    </div>
                </div>
                <div v-if="invitedHint">
                    <p class="m-0 text-success">Invited {{ invitedHint }}. Want to invite anybody else?</p>
                </div>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useUserStore } from '@/stores/UserStore.js';

    const props = defineProps({
        project: {
            type: Object,
            required: true,
        },
    });
    const addProjectUserEmail = ref('');
    const showPopover = ref(false);
    const invitedHint = ref(null);
    const hasValidEmail = computed(() => {
        /**
         * Code taken from https://stackoverflow.com/questions/7635533/validate-email-address-textbox-using-javascript on 10/12/2024
         * Adapted to my needs and minimalised the code.
         */
        var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

        return reg.test(addProjectUserEmail.value ?? '');
    });
    const userStore = useUserStore();

    const onSubmitEmail = () => {
        if (!hasValidEmail) {
            return;
        }

        // @todo implement this
        userStore.createUserProjectInvitation(props.project, addProjectUserEmail.value).then(() => {
            invitedHint.value = addProjectUserEmail.value;
            addProjectUserEmail.value = '';
        });
    };
</script>