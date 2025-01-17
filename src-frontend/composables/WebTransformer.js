import { pipeline } from '@huggingface/transformers';

/**
 * This web transformer can be used to transform text using pre-trained models with the web GPU, not requiring a server.
 * We use the library https://github.com/huggingface/transformers.js to accomplish this
 */
export function  useWebTransformer() {
    const summarize = async (text) => {
        console.log('Summarizing:', text);
        const startTime = new Date().getTime();
        const generator = await pipeline('summarization', 'Xenova/distilbart-cnn-6-6');
        const output = await generator(text);

        const endTime = new Date().getTime();
        console.log(output, 'Time taken:', endTime - startTime, 'ms');

        // Allocate a pipeline for sentiment-analysis
        const pipe = await pipeline('sentiment-analysis');

        const sentimentOutput = await pipe('I love transformers!');
        // [{'label': 'POSITIVE', 'score': 0.999817686}]

        console.log(sentimentOutput);

        return output;
    };

    return {
        summarize,
    };
};