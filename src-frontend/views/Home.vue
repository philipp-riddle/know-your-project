<template>
    <h3>@tbd @todo</h3>
</template>

<script setup>
    import { onMounted } from 'vue';
    import { useRouter } from 'vue-router';

    const router = useRouter();

    onMounted(() => {
        router.push({ name: 'Wiki' }); // for now, redirect to wiki
    });
</script>