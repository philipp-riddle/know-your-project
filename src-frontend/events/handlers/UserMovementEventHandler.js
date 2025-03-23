import { useUserMovementStore } from '@/stores/UserMovementStore';

/**
 * Handles user movement events.
 * Each events contains information about the user who moved, the x and y coordinates, and the route name.
 */
export function useUserMovementEventHandler() {
    const userMovementStore = useUserMovementStore();

    const handle = (event) => {
        userMovementStore.registerListenedMovement(
            event.user, 
            event.relativeX,
            event.relativeY,
            event.routeName,
            event.hoveredElementDomPath,
            event.hoveredElementOffsetRelativeX,
            event.hoveredElementOffsetRelativeY,
        );
    };

    return {
        handle,
    };
};