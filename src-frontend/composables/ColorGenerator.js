export function useColorGenerator() {
    const generateHexColor = () => {
        const characters = '0123456789ABCDEF';
        const colorLength = 6;

        let color = '#';

        for (let i = 0; i < colorLength; i++) {
            color += characters[Math.floor(Math.random() * 16)];
        }

        return color;
    }

    return {
        generateHexColor,
    }
}