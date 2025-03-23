import { useColorGenerator } from '@/composables/ColorGenerator';
import { useDOMPath } from '@/composables/DOMPath';
import { fetchRegisterMovement } from '@/stores/fetch/UserFetcher';
import { useProjectStore } from '@/stores/ProjectStore';
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useMouse, useThrottleFn } from '@vueuse/core';
import { defineStore } from 'pinia';

export const useUserMovementStore = defineStore('userMovement', () => {
    const colorGenerator = useColorGenerator();
    const domPath = useDOMPath();
    const projectStore = useProjectStore();
    const currentRoute = useRoute();
    const userPositions = ref({});

    const currentRegisterPromise = ref(null);
    const invalidateUserPositionsInterval = ref(null); // every 20 seconds we invalidate the user positions; we set this in the setup function
    
    /**
     * Use the vueUse component to track user mouse movements.
     * Whenever the value changes we register the movement with vue watchers.
     */
    const { x, y, sourceType } = useMouse({
        throttle: 500,
        touch: true,
        pointer: true,
        source: window,
        force: false,
    });

    watch (() => x.value, () => {
        throttledMouseMovement();
    });

    watch(() => y.value, () => {
        throttledMouseMovement();
    });

    const throttledMouseMovement = useThrottleFn(() => {
        if (currentRoute.name) { // avoid early movements before the vue router is initialized
            registerMovement(x.value, y.value, currentRoute.name);
        }
    }, 200);

    const registerMovement = (x, y, routeName) => {
        if (currentRegisterPromise.value) {
            return;
        }

        const hoveredElement = getHoveredElement();

        if (!hoveredElement) {
            console.error('No hovered element found');
            return;
        }

        /**
         * This is a technique to get even more accurate coordinates of the mouse movement.
         * By calculating the relative position of the mouse movement within the hovered element, we can better replicate its position for other users.
         */
        const hoveredElementFullDomPath = domPath.getFullPath(hoveredElement);
        const hoveredElementRectangle = hoveredElement.getBoundingClientRect();
        const hoveredElementMovementOffsetRelativeX = (x - hoveredElementRectangle.left) / hoveredElementRectangle.width;
        const hoveredElementMovementOffsetRelativeY = (y - hoveredElementRectangle.top) / hoveredElementRectangle.height;

        currentRegisterPromise.value = fetchRegisterMovement(
            projectStore.selectedProject.id,
            routeName,
            x,
            y,
            hoveredElementFullDomPath,
            hoveredElementMovementOffsetRelativeX,
            hoveredElementMovementOffsetRelativeY,
        ).then(() => {
            currentRegisterPromise.value = null;
        });
    };

    const getHoveredElement = () => {
        var n = document.querySelector(":hover");
        var nn;

        while (n) {
            nn = n;
            n = nn.querySelector(":hover");
        }

        return nn;
    }

    const registerListenedMovement = (user, relativeX, relativeY, routeName, hoveredElementDomPath, hoveredElementOffsetRelativeX, hoveredElementOffsetRelativeY) => {
        let absoluteX = null;
        let absoluteY = null;
        
        if (hoveredElementDomPath && hoveredElementOffsetRelativeX && hoveredElementOffsetRelativeY) {
            const hoveredElement = domPath.getElementAt(hoveredElementDomPath);

            // the hover element coordinates are nice to have, but not necessary.
            // if not found we will work with the absolute x and y of the client.
            if (hoveredElement) {
                const hoveredElementRectangle = hoveredElement.getBoundingClientRect();
                absoluteX = hoveredElementRectangle.x + hoveredElementOffsetRelativeX * hoveredElementRectangle.width;
                absoluteY = hoveredElementRectangle.y + hoveredElementOffsetRelativeY * hoveredElementRectangle.height;
            }
        }

        if (!absoluteX || !absoluteY) {
            return;
        }

        // convert it back to absolute coordinates from relative coordinates
        absoluteX = absoluteX ?? relativeX * window.screen.width;
        absoluteY = absoluteY ?? relativeY * window.screen.height;

        userPositions.value[user.id] = {
            user: user,
            x: absoluteX,
            y: absoluteY,
            routeName: routeName,
            ttl: Date.now() + 1000 * 20, // every movement is valid for 20 seconds; i.e. user is online for 20 seconds
            color: userPositions.value[user.id]?.color ?? colorGenerator.generateHexColor(),
        };
    };

    const invalidateUserPositions = () => {
        const now = Date.now();

        for (const [key, value] of Object.entries(userPositions.value)) {
            if (value.ttl < now) {
                delete userPositions.value[key];
            }
        }
    }

    const setup = () => {
        invalidateUserPositionsInterval.value = setInterval(invalidateUserPositions, 1000 * 20);
    }

    return {
        userPositions,
        registerMovement,
        registerListenedMovement,
        setup,
    };
});