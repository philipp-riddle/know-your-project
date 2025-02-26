<template>
    <div class="d-flex flex-column align-items-center">
        <h2>Welcome to the Know Your Project beta</h2>
        <p class="text-muted">A collaborative space that integrates project and knowledge management into one tool.</p>
    </div>


    <div class="row w-100">
        <div class="col-sm-12 offset-md-3 col-md-6 p-0">
            <div class="row">
                <div class="col-sm-12 w-100">
                    <div class="card setup-card w-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex flex-row justify-content-between">
                                <p class="bold card-title m-0">{{ conceptSteps[0].title }}</p>
                                <p class="m-0"><span class="badge rounded-pill bg-dark">{{ conceptSteps[0].id }}</span></p>
                            </div>
                            <p class="text-muted m-0"><span v-html="conceptSteps[0].description"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col" v-for="step in conceptSteps.slice(1, 3)" :key="step.id">
                    <div class="card setup-card w-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex flex-row justify-content-between">
                                <p class="bold card-title m-0">{{ step.title }}</p>
                                <p class="m-0"><span class="badge rounded-pill bg-dark">{{ step.id }}</span></p>
                            </div>
                            <p class="text-muted m-0"><span v-html="step.description"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col" v-for="step in conceptSteps.slice(3, 5)" :key="step.id">
                    <div class="card setup-card w-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex flex-row justify-content-between">
                                <p class="bold card-title m-0">{{ step.title }}</p>
                                <p class="m-0"><span class="badge rounded-pill bg-dark">{{ step.id }}</span></p>
                            </div>
                            <div class="d-flex flex-row align-items-center gap-2">
                                <p class="text-muted m-0"><span v-html="step.description"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 w-100">
                    <div class="card setup-card w-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex flex-row justify-content-between">
                                <p class="bold card-title m-0">{{ conceptSteps[conceptSteps.length - 1].title }}</p>
                                <p class="m-0"><span class="badge rounded-pill bg-dark">{{ conceptSteps[conceptSteps.length - 1].id }}</span></p>
                            </div>

                            <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                                <p class="text-muted m-0"><span v-html="conceptSteps[conceptSteps.length - 1].description"></span></p>
                                <button
                                    class="btn btn-lg btn-dark"
                                    @click="emit('continue')"
                                >
                                    <font-awesome-icon :icon="['fas', 'chevron-right']" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

    const emit = defineEmits(['continue']);
    const conceptSteps = [
        {
            id: 1,
            title: 'Kanban meets Double Diamond',
            description: 'Organize your projects visually using a <strong>Kanban board</strong>, while embedding structured creativity with the <strong>Double Diamond design process</strong>. This ensures smooth transitions between discovering problems, defining solutions, developing ideas, and delivering results. What <i>\'To-Do, Doing, Done\'</i> was is now <i>\'Discover, Define, Develop, Deliver\'</i>.',
        },
        {
            id: 2,
            title: 'Task-Centric Knowledge Management',
            description: 'Unlike traditional project tools, tasks in Know Your Project go beyond to-do lists—they serve as <strong>knowledge hubs</strong>. Each task can hold discussions, documentation, and AI-enhanced insights, keeping information structured and actionable.',
        },
        {
            id: 3,
            title: 'AI-Powered search & assistance',
            description: 'Navigate your projects effortlessly with <strong>full-text and similarity search</strong>. AI-enhanced features allow you to ask the project questions, generate summaries, and surface relevant knowledge without switching tools.',
        },
        {
            id: 4,
            title: 'Integrated Wiki for seamless documentation',
            description: 'Create, structure, and interconnect pages just like in Notion, but fully integrated into your project workflow. Convert ideas into structured knowledge and ensure critical insights are always at hand.',
        },
        {
            id: 5,
            title: 'Collaboration designed for teams',
            description: 'Tag colleagues, assign tasks, and track progress; all in one place. Real-time updates and calendar integrations ensure everyone stays on the same page, reducing miscommunication and knowledge silos.',
        },
        {
            id: 6,
            title: 'Your Next Step: Setting Up Your Project',
            description: 'Let’s get started! In the next steps, you will set up your first project, invite your team, and explore key features.',
        }
    ];
</script>