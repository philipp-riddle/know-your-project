<template>
    <div class="d-flex flex-row justify-content-center">
        <div class="d-flex flex-column align-items-center gap-4">
            <div class="d-flex flex-column align-items-center">
                <h3>Done!</h3>
                <p>You are ready to use the <span class="bold">Know Your Project</span> beta.</p>
                <p>
                    If you run into any issues or have any feedback reach out <a href="mailto:know-your-project@philippmartini.de">via email</a>.
                    Anyways, an additional beta feedback survey awaits you later...
                </p>
            </div>
            <button class="btn btn-wide btn-dark" @click="$emit('continue')">
                Continue
            </button>
        </div>
    </div>
</template>

<script setup>
    const emit = defineEmits(['continue']);
</script>