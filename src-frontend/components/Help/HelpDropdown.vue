<template>
    <VDropdown>
        <button class="nav-link btn btn-dark d-flex flex-row gap-3">
            <font-awesome-icon :icon="['fas', 'question-circle']" />
            Help
        </button>
        
        <template #popper>
            <div class="p-2 d-flex flex-row justify-content-between gap-2" style="min-width: 15rem">
                <router-link
                    :to="{ name: 'Help' }"
                    class="btn btn-dark-gray"
                >
                    <font-awesome-icon :icon="['fas', 'question-circle']" />
                    Help
                </router-link>
                <router-link
                    :to="{ name: 'Feedback' }"
                    class="btn btn-dark-gray"
                >
                    <font-awesome-icon :icon="['fas', 'comment']" />
                    Feedback
                </router-link>
            </div>
        </template>

</template>