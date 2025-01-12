<template>
    <div class="d-flex flex-column gap-2">
        <div class="card">
            <div class="card-header p-2">
                <span v-html="threadItem.itemPrompt.prompt.promptText"></span>
            </div>
            <div class="card-body p-2">
                <span v-html="assistantResponse"></span>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        threadItem: {
            type: Object,
            required: true,
        },
    });

    /**
     * We do not want the whole response to the user - instead we show the <h3> heading the AI already provided. (this instruction is coded into the GenerationEngine)
     */
    const assistantResponse = computed(() => {
        const response = props.threadItem.itemPrompt.prompt.responseText;

        if (response === null || response.trim() === '') {
            return '<i>Empty response</i>';
        }

        if (response.startsWith('<')) {
            var htmlEmbedding = document.createElement( 'html' );
            htmlEmbedding.innerHTML = response;

            for (var i = 0; i < htmlEmbedding.querySelector('body').children.length; i++) {
                if (htmlEmbedding.querySelector('body').children[i].tagName.startsWith('H')) { // found a tag that is a heading
                    var headingText = htmlEmbedding.querySelector('body').children[i].innerText;
                    headingText = headingText.trim(':'); // remove any trailing colon

                    return headingText;
                }
            }
        }

        return response;
    })
</script>